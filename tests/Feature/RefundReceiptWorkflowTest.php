<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Child;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundReceiptWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_cancellation_automatically_records_refund(): void
    {
        [$parent, $booking] = $this->createBooking();
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->createPaidPayment($booking);

        $bookingRequest = $booking->bookingRequests()->create([
            'request_type' => 'cancellation',
            'reason' => 'Family plan changed.',
            'request_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(
                route(
                    'admin.booking-requests.approve',
                    $bookingRequest
                ),
                ['admin_note' => 'Approved with full refund.']
            )
            ->assertSessionHas('success');

        $booking->refresh();
        $payment->refresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertNotNull($payment->refunded_at);
        $this->assertSame('1500.00', $payment->refund_amount);
        $this->assertSame('refunded', $payment->display_status);
    }

    public function test_admin_can_record_refund_for_legacy_cancelled_paid_booking(): void
    {
        [, $booking] = $this->createBooking();
        $booking->update(['status' => 'cancelled']);

        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->createPaidPayment($booking);

        $this->actingAs($admin)
            ->post(route('admin.payments.refund', $payment), [
                'refund_note' => 'Legacy cancellation refund.',
            ])
            ->assertRedirect(route('admin.payments.show', $payment))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'payment_id' => $payment->payment_id,
            'refund_amount' => 1500,
            'refund_note' => 'Legacy cancellation refund.',
        ]);

        $this->assertNotNull($payment->fresh()->refunded_at);
    }

    public function test_refund_is_blocked_while_booking_is_confirmed(): void
    {
        [, $booking] = $this->createBooking();
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->createPaidPayment($booking);

        $this->actingAs($admin)
            ->post(route('admin.payments.refund', $payment), [
                'refund_note' => 'Should not be allowed.',
            ])
            ->assertSessionHas('error');

        $this->assertNull($payment->fresh()->refunded_at);
    }

    public function test_parent_can_view_receipt_for_paid_payment(): void
    {
        [$parent, $booking] = $this->createBooking();
        $payment = $this->createPaidPayment($booking);

        $this->actingAs($parent)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Payment Receipt')
            ->assertSee($booking->display_reference)
            ->assertSee('Print / Save as PDF');
    }

    public function test_parent_cannot_view_another_parents_receipt(): void
    {
        [$firstParent] = $this->createBooking('First Receipt Child');
        [, $secondBooking] = $this->createBooking('Second Receipt Child');
        $payment = $this->createPaidPayment(
            $secondBooking,
            'TXN-RECEIPT-002'
        );

        $this->actingAs($firstParent)
            ->get(route('payments.receipt', $payment))
            ->assertForbidden();
    }

    public function test_admin_can_filter_refunded_payments(): void
    {
        [, $booking] = $this->createBooking();
        $booking->update(['status' => 'cancelled']);
        $payment = $this->createPaidPayment($booking);
        $payment->update([
            'refund_amount' => $payment->amount,
            'refunded_at' => now(),
            'refund_note' => 'Filter test refund.',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.payments.index', [
                'status' => 'refunded',
            ]))
            ->assertOk()
            ->assertSee('PAY-' . $payment->payment_id)
            ->assertSee('refunded');
    }

    private function createBooking(
        string $childName = 'Refund Test Child'
    ): array {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();

        $child = $profile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Refund Test Service ' . uniqid(),
            'price' => 1500,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'confirmed',
            'total_amount' => 1500,
        ]);

        return [$parent, $booking];
    }

    private function createPaidPayment(
        Booking $booking,
        string $transactionId = 'TXN-REFUND-001'
    ): Payment {
        return $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => 'mobile-banking',
            'transaction_id' => $transactionId,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
