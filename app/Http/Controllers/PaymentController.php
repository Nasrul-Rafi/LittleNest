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
                    str_replace('-', ' ', $payment->payment_method),
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

        $latestPayment = $booking->payments()
            ->latest('payment_id')
            ->first();

        if (
            $latestPayment
            && !$latestPayment->isRefunded()
            && in_array(
                $latestPayment->payment_status,
                ['pending', 'paid'],
                true
            )
        ) {
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

        $latestPayment = $booking->payments()
            ->latest('payment_id')
            ->first();

        if (
            $latestPayment
            && !$latestPayment->isRefunded()
            && in_array(
                $latestPayment->payment_status,
                ['pending', 'paid'],
                true
            )
        ) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'This booking already has an active payment.');
        }

        $validated = $request->validate([
            'payment_method' => [
                'required',
                'in:cash,card,mobile-banking',
            ],
            'transaction_id' => [
                'nullable',
                'string',
                'max:100',
                'unique:payments,transaction_id',
            ],
        ]);

        if (
            $validated['payment_method'] !== 'cash'
            && empty($validated['transaction_id'])
        ) {
            return back()
                ->withErrors([
                    'transaction_id' =>
                        'Transaction ID is required for card or mobile banking.',
                ])
                ->withInput();
        }

        $payment = $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'payment_status' => 'pending',
        ]);

        return redirect()
            ->route('payments.show', $payment)
            ->with(
                'success',
                'Payment submitted successfully. Admin confirmation is pending.'
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
