<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        return view('payments.create', compact('booking'));
    }

    public function store(Request $request, Booking $booking)
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

        $request->validate([
            'payment_method' => ['required', 'in:mobile-banking'],
            'mobile_number' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'demo_confirmation' => ['accepted'],
        ], [
            'mobile_number.regex' => 'Enter a valid 11 digit Bangladeshi mobile number.',
            'demo_confirmation.accepted' => 'Please confirm that you understand this is a demo payment.',
        ]);

        $payment = $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => 'mobile-banking',
            'transaction_id' => null,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $payment->transaction_id = 'SIM-LN-' . str_pad(
            (string) $payment->payment_id,
            6,
            '0',
            STR_PAD_LEFT
        );
        $payment->save();

        return redirect()
            ->route('payments.show', $payment)
            ->with(
                'success',
                'Demo payment completed successfully. No real money was charged.'
            );
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
