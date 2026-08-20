<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\SslCommerzService;
use Illuminate\Http\Request;
use Throwable;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $query = Payment::with([
            'booking.child.parentProfile.user',
            'booking.service',
        ]);

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        if ($search !== '') {
            $query->where(function ($paymentQuery) use ($search) {
                $paymentQuery
                    ->where('transaction_id', 'like', '%' . $search . '%')
                    ->orWhereHas('booking', function ($bookingQuery) use ($search) {
                        $bookingQuery->where(
                            'booking_reference',
                            'like',
                            '%' . $search . '%'
                        );
                    })
                    ->orWhereHas(
                        'booking.child.parentProfile.user',
                        function ($userQuery) use ($search) {
                            $userQuery->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    )
                    ->orWhereHas('booking.child', function ($childQuery) use ($search) {
                        $childQuery->where(
                            'full_name',
                            'like',
                            '%' . $search . '%'
                        );
                    });
            });
        }

        if ($status === 'refunded') {
            $query->whereNotNull('refunded_at');
        } elseif ($status === 'paid') {
            $query->where('payment_status', 'paid')
                ->whereNull('refunded_at');
        } elseif (in_array($status, ['pending', 'failed'], true)) {
            $query->where('payment_status', $status);
        }

        $payments = $query
            ->latest('payment_id')
            ->get();

        return view('admin.payments.index', compact(
            'payments',
            'search',
            'status'
        ));
    }

    public function show(Request $request, Payment $payment)
    {
        $this->ensureAdmin($request);

        $payment->load([
            'booking.child.parentProfile.user',
            'booking.service',
        ]);

        return view('admin.payments.show', compact('payment'));
    }

    public function markPaid(Request $request, Payment $payment)
    {
        $this->ensureAdmin($request);

        if ($payment->gateway_name === 'sslcommerz') {
            return back()->with(
                'error',
                'SSLCOMMERZ payments must be confirmed by gateway validation.'
            );
        }

        if ($payment->payment_status !== 'pending') {
            return back()->with(
                'error',
                'Only pending payments can be marked as paid.'
            );
        }

        if ($payment->booking->status !== 'confirmed') {
            return back()->with(
                'error',
                'Payment cannot be completed because the booking is not confirmed.'
            );
        }

        $payment->payment_status = 'paid';
        $payment->paid_at = now();
        $payment->save();

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment marked as paid successfully.');
    }

    public function markFailed(Request $request, Payment $payment)
    {
        $this->ensureAdmin($request);

        if ($payment->gateway_name === 'sslcommerz') {
            return back()->with(
                'error',
                'SSLCOMMERZ payment status is controlled by the gateway.'
            );
        }

        if ($payment->payment_status !== 'pending') {
            return back()->with(
                'error',
                'Only pending payments can be marked as failed.'
            );
        }

        $payment->payment_status = 'failed';
        $payment->paid_at = null;
        $payment->save();

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment marked as failed.');
    }

    public function refund(
        Request $request,
        Payment $payment,
        SslCommerzService $sslCommerz
    ) {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'refund_note' => ['required', 'string', 'max:1000'],
        ]);

        if ($payment->payment_status !== 'paid') {
            return back()->with(
                'error',
                'Only paid payments can be refunded.'
            );
        }

        if ($payment->isRefunded()) {
            return back()->with(
                'error',
                'This payment has already been refunded.'
            );
        }

        if ($payment->booking->status !== 'cancelled') {
            return back()->with(
                'error',
                'The booking must be cancelled before a refund is recorded.'
            );
        }

        if ($payment->gateway_name === 'sslcommerz') {
            if ($payment->refund_reference) {
                return back()->with(
                    'error',
                    'A refund request has already been sent to SSLCOMMERZ.'
                );
            }

            try {
                $data = $sslCommerz->initiateRefund(
                    $payment,
                    $validated['refund_note']
                );
            } catch (Throwable $exception) {
                return back()->with('error', $exception->getMessage());
            }

            $status = strtolower((string) ($data['status'] ?? 'failed'));
            $refundReference = $data['refund_ref_id'] ?? null;

            if (
                ($data['APIConnect'] ?? null) !== 'DONE'
                || !in_array($status, ['success', 'processing'], true)
                || !$refundReference
            ) {
                return back()->with(
                    'error',
                    (string) ($data['errorReason'] ?? 'SSLCOMMERZ did not accept the refund request.')
                );
            }

            $payment->update([
                'refund_amount' => $payment->amount,
                'refund_note' => $validated['refund_note'],
                'refund_reference' => $refundReference,
                'refund_gateway_status' => $status,
            ]);

            return redirect()
                ->route('admin.payments.show', $payment)
                ->with(
                    'success',
                    'Refund request sent to SSLCOMMERZ. Use Check Refund Status until the sandbox confirms it.'
                );
        }

        $payment->update([
            'refund_amount' => $payment->amount,
            'refunded_at' => now(),
            'refund_note' => $validated['refund_note'],
        ]);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment refund recorded successfully.');
    }

    public function checkRefundStatus(
        Request $request,
        Payment $payment,
        SslCommerzService $sslCommerz
    ) {
        $this->ensureAdmin($request);

        if (
            $payment->gateway_name !== 'sslcommerz'
            || !$payment->refund_reference
        ) {
            return back()->with('error', 'No SSLCOMMERZ refund request is available for this payment.');
        }

        try {
            $data = $sslCommerz->queryRefund($payment->refund_reference);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $status = strtolower((string) ($data['status'] ?? 'unknown'));

        $update = [
            'refund_gateway_status' => $status,
        ];

        if ($status === 'refunded') {
            $update['refunded_at'] = now();
        }

        $payment->update($update);

        if ($status === 'refunded') {
            return back()->with('success', 'SSLCOMMERZ confirmed the refund successfully.');
        }

        return back()->with(
            'success',
            'Current SSLCOMMERZ refund status: ' . $status . '.'
        );
    }

    private function ensureAdmin(Request $request): void
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }
    }
}
