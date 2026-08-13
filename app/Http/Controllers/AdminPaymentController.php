<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $payments = Payment::with([
            'booking.child.parentProfile.user',
            'booking.service',
        ])
            ->latest('payment_id')
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Request $request, Payment $payment)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $payment->load([
            'booking.child.parentProfile.user',
            'booking.service',
        ]);

        return view('admin.payments.show', compact('payment'));
    }

    public function markPaid(Request $request, Payment $payment)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
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
        if ($request->user()->role !== 'admin') {
            abort(403);
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
}
