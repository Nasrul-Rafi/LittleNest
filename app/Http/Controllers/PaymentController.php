<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\SslCommerzService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'parent') {
            abort(403);
        }

        $parentProfile = $request->user()->parentProfile()->firstOrCreate();

        $payments = Payment::with(['booking.child', 'booking.service'])
            ->whereHas('booking.child', function ($query) use ($parentProfile) {
                $query->where(
                    'parent_profile_id',
                    $parentProfile->parent_profile_id
                );
            })
            ->latest('payment_id')
            ->get();

        return view('payments.index', compact('payments'));
    }

    public function export(Request $request): StreamedResponse
    {
        if ($request->user()->role !== 'parent') {
            abort(403);
        }

        $parentProfile = $request->user()->parentProfile()->firstOrCreate();

        $payments = Payment::with(['booking.child', 'booking.service'])
            ->whereHas('booking.child', function ($query) use ($parentProfile) {
                $query->where(
                    'parent_profile_id',
                    $parentProfile->parent_profile_id
                );
            })
            ->latest('payment_id')
            ->get();

        $fileName = 'littlenest-payment-history-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Payment',
                'Booking',
                'Child',
                'Service',
                'Amount',
                'Method',
                'Status',
                'Transaction ID',
                'Date',
            ]);

            foreach ($payments as $payment) {
                $date = $payment->refunded_at
                    ?? $payment->paid_at
                    ?? $payment->created_at;

                fputcsv($handle, [
                    'PAY-' . $payment->payment_id,
                    $payment->booking->display_reference,
                    $payment->booking->child->full_name,
                    $payment->booking->service->name,
                    number_format((float) $payment->amount, 2, '.', ''),
                    $payment->display_method,
                    $payment->display_status,
                    $payment->transaction_id ?? '',
                    $date?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(Request $request, Booking $booking)
    {
        $this->ensureParentOwnership($request, $booking);

        if ($booking->status !== 'confirmed') {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'Payment is available only for confirmed bookings.');
        }

        if ($this->hasActivePayment($booking)) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'This booking already has an active payment.');
        }

        $booking->loadMissing(['child', 'service']);

        return view('payments.create', compact('booking'));
    }

    public function store(
        Request $request,
        Booking $booking,
        SslCommerzService $sslCommerz
    ) {
        $this->ensureParentOwnership($request, $booking);

        if ($booking->status !== 'confirmed') {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'Payment is available only for confirmed bookings.');
        }

        if ($this->hasActivePayment($booking)) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'This booking already has an active payment.');
        }

        $validated = $request->validate([
            'customer_phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
        ], [
            'customer_phone.regex' => 'Enter a valid 11 digit Bangladeshi mobile number.',
        ]);

        if (!$sslCommerz->isConfigured()) {
            return back()->with(
                'error',
                'SSLCOMMERZ is not configured. Add the sandbox Store ID and Store Password in the .env file.'
            );
        }

        $booking->loadMissing([
            'service',
            'child.parentProfile.user',
        ]);

        $payment = $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => 'card',
            'gateway_name' => 'sslcommerz',
            'gateway_status' => 'initiating',
            'transaction_id' => $this->generateTransactionId($booking),
            'payment_status' => 'pending',
            'paid_at' => null,
        ]);

        try {
            $session = $sslCommerz->createSession(
                $payment,
                $booking,
                $request->user(),
                $validated['customer_phone']
            );

            $payment->update([
                'gateway_status' => 'session_created',
                'gateway_session_key' => $session['sessionkey'] ?? null,
            ]);

            return redirect()->away($session['GatewayPageURL']);
        } catch (Throwable $exception) {
            $payment->update([
                'payment_status' => 'failed',
                'gateway_status' => 'session_failed',
            ]);

            return redirect()
                ->route('payments.create', $booking)
                ->with('error', $exception->getMessage());
        }
    }

    public function sslSuccess(
        Request $request,
        SslCommerzService $sslCommerz
    ): RedirectResponse {
        $payment = $this->findGatewayPayment($request);

        if (!$payment) {
            return redirect()
                ->route('home')
                ->with('error', 'The SSLCOMMERZ payment could not be matched with a LittleNest transaction.');
        }

        if ($payment->payment_status === 'paid') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'Payment was already confirmed successfully.');
        }

        $validationId = trim((string) $request->input('val_id'));

        if ($validationId === '') {
            $payment->update([
                'payment_status' => 'failed',
                'gateway_status' => 'missing_validation_id',
            ]);

            return redirect()
                ->route('payments.show', $payment)
                ->with('error', 'SSLCOMMERZ returned an incomplete payment response.');
        }

        try {
            $data = $sslCommerz->validate($validationId);

            if (!$sslCommerz->validationMatches($payment, $data)) {
                $payment->update([
                    'payment_status' => 'failed',
                    'gateway_status' => strtolower((string) ($data['status'] ?? 'validation_failed')),
                    'validation_id' => $validationId,
                ]);

                return redirect()
                    ->route('payments.show', $payment)
                    ->with('error', 'The payment could not be verified by SSLCOMMERZ.');
            }

            $this->markGatewayPaymentPaid($payment, $data, $validationId);

            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'Payment completed and verified successfully through SSLCOMMERZ.');
        } catch (Throwable $exception) {
            $payment->update([
                'gateway_status' => 'validation_error',
            ]);

            return redirect()
                ->route('payments.show', $payment)
                ->with('error', $exception->getMessage());
        }
    }

    public function sslFail(Request $request): RedirectResponse
    {
        $payment = $this->findGatewayPayment($request);

        if (!$payment) {
            return redirect()
                ->route('home')
                ->with('error', 'The failed SSLCOMMERZ transaction could not be matched.');
        }

        if ($payment->payment_status !== 'paid') {
            $payment->update([
                'payment_status' => 'failed',
                'gateway_status' => 'failed',
                'paid_at' => null,
            ]);
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with('error', 'The SSLCOMMERZ payment failed. You can try again.');
    }

    public function sslCancel(Request $request): RedirectResponse
    {
        $payment = $this->findGatewayPayment($request);

        if (!$payment) {
            return redirect()
                ->route('home')
                ->with('error', 'The cancelled SSLCOMMERZ transaction could not be matched.');
        }

        if ($payment->payment_status !== 'paid') {
            $payment->update([
                'payment_status' => 'failed',
                'gateway_status' => 'cancelled',
                'paid_at' => null,
            ]);
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with('error', 'The SSLCOMMERZ payment was cancelled. You can try again.');
    }

    public function sslIpn(
        Request $request,
        SslCommerzService $sslCommerz
    ) {
        $payment = $this->findGatewayPayment($request);

        if (!$payment) {
            return response('Payment not found.', 404);
        }

        $status = strtoupper((string) $request->input('status'));

        if (in_array($status, ['FAILED', 'CANCELLED'], true)) {
            if ($payment->payment_status !== 'paid') {
                $payment->update([
                    'payment_status' => 'failed',
                    'gateway_status' => strtolower($status),
                    'paid_at' => null,
                ]);
            }

            return response('Payment status updated.', 200);
        }

        $validationId = trim((string) $request->input('val_id'));

        if ($validationId === '') {
            return response('Validation ID is missing.', 422);
        }

        try {
            $data = $sslCommerz->validate($validationId);

            if (!$sslCommerz->validationMatches($payment, $data)) {
                $payment->update([
                    'gateway_status' => 'ipn_validation_failed',
                    'validation_id' => $validationId,
                ]);

                return response('Payment validation failed.', 422);
            }

            $this->markGatewayPaymentPaid($payment, $data, $validationId);

            return response('Payment verified.', 200);
        } catch (Throwable $exception) {
            $payment->update([
                'gateway_status' => 'ipn_validation_error',
            ]);

            return response('Payment validation error.', 500);
        }
    }

    public function checkStatus(
        Request $request,
        Payment $payment,
        SslCommerzService $sslCommerz
    ): RedirectResponse {
        $payment->loadMissing('booking.child');
        $this->ensureParentOwnership($request, $payment->booking);

        if ($payment->gateway_name !== 'sslcommerz') {
            return back()->with('error', 'This payment is not an SSLCOMMERZ transaction.');
        }

        if ($payment->payment_status === 'paid') {
            return back()->with('success', 'This payment is already confirmed as paid.');
        }

        try {
            $data = $sslCommerz->queryTransaction((string) $payment->transaction_id);
            $transaction = $sslCommerz->latestTransaction($data);

            if (!$transaction) {
                return back()->with('error', 'SSLCOMMERZ has not returned a completed transaction yet.');
            }

            $validationId = trim((string) ($transaction['val_id'] ?? ''));

            if (
                $validationId !== ''
                && $sslCommerz->validationMatches($payment, $transaction)
            ) {
                $this->markGatewayPaymentPaid($payment, $transaction, $validationId);

                return back()->with('success', 'Payment status verified successfully from SSLCOMMERZ.');
            }

            $status = strtolower((string) ($transaction['status'] ?? 'pending'));

            $payment->update([
                'gateway_status' => $status,
                'payment_status' => in_array($status, ['failed', 'cancelled'], true)
                    ? 'failed'
                    : 'pending',
            ]);

            return back()->with('error', 'The payment is not confirmed as successful by SSLCOMMERZ yet.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function show(Request $request, Payment $payment)
    {
        $payment->load([
            'booking.child',
            'booking.service',
        ]);

        $this->ensureParentOwnership($request, $payment->booking);

        return view('payments.show', compact('payment'));
    }

    public function receipt(Request $request, Payment $payment)
    {
        $payment->load([
            'booking.child.parentProfile.user',
            'booking.service',
        ]);

        $this->ensureParentOwnership($request, $payment->booking);

        if (
            $payment->payment_status !== 'paid'
            && !$payment->isRefunded()
        ) {
            return redirect()
                ->route('payments.show', $payment)
                ->with(
                    'error',
                    'Receipt is available after the payment is confirmed.'
                );
        }

        return view('payments.receipt', compact('payment'));
    }

    private function markGatewayPaymentPaid(
        Payment $payment,
        array $data,
        string $validationId
    ): void {
        $cardType = (string) ($data['card_type'] ?? '');
        $paymentMethod = $this->gatewayPaymentMethod($cardType);

        $payment->update([
            'payment_method' => $paymentMethod,
            'payment_status' => 'paid',
            'gateway_status' => strtolower((string) ($data['status'] ?? 'valid')),
            'validation_id' => $validationId,
            'bank_transaction_id' => $data['bank_tran_id'] ?? null,
            'card_type' => $cardType !== '' ? $cardType : null,
            'paid_at' => now(),
        ]);
    }

    private function gatewayPaymentMethod(string $cardType): string
    {
        $value = strtolower($cardType);

        foreach (['bkash', 'nagad', 'rocket', 'upay', 'mobile'] as $keyword) {
            if (str_contains($value, $keyword)) {
                return 'mobile-banking';
            }
        }

        return 'card';
    }

    private function findGatewayPayment(Request $request): ?Payment
    {
        $transactionId = trim((string) $request->input('tran_id'));

        if ($transactionId === '') {
            return null;
        }

        return Payment::where('transaction_id', $transactionId)
            ->where('gateway_name', 'sslcommerz')
            ->first();
    }

    private function generateTransactionId(Booking $booking): string
    {
        do {
            $transactionId = 'LN'
                . $booking->booking_id
                . now()->format('ymdHis')
                . strtoupper(Str::random(4));
        } while (Payment::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    private function hasActivePayment(Booking $booking): bool
    {
        $latestPayment = $booking->payments()
            ->latest('payment_id')
            ->first();

        return $latestPayment
            && !$latestPayment->isRefunded()
            && in_array(
                $latestPayment->payment_status,
                ['pending', 'paid'],
                true
            );
    }

    private function ensureParentOwnership(
        Request $request,
        Booking $booking
    ): void {
        if ($request->user()->role !== 'parent') {
            abort(403);
        }

        $parentProfile = $request->user()
            ->parentProfile()
            ->firstOrCreate();

        $booking->loadMissing('child');

        if (
            $booking->child->parent_profile_id
            !== $parentProfile->parent_profile_id
        ) {
            abort(403);
        }
    }
}
