<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
        $payment->load('booking.child');
        $this->ensureParentOwnership($request, $payment->booking);

        return view('payments.show', compact('payment'));
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
