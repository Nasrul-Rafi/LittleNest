<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Child;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createParentAndChild(
        string $childName = 'Payment Child'
    ): array {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $parentProfile = $parent->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        return [$parent, $child];
    }

    private function createBooking(
        Child $child,
        string $status = 'confirmed'
    ): Booking {
        $service = Service::create([
            'name' => 'Payment Test Service',
            'price' => 1500,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        return $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => $status,
            'total_amount' => 1500,
        ]);
    }

    private function createPayment(
        Booking $booking,
        string $status = 'pending',
        ?string $transactionId = 'TXN-1001'
    ): Payment {
        return $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => 'mobile-banking',
            'transaction_id' => $transactionId,
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);
    }

    public function test_parent_can_submit_payment_for_confirmed_booking(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $response = $this
            ->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'transaction_id' => 'TXN-PARENT-001',
                'amount' => 1,
                'payment_status' => 'paid',
            ]);

        $payment = Payment::first();

        $this->assertNotNull($payment);
        $response->assertRedirect(
            route('payments.show', $payment)
        );

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->booking_id,
            'amount' => 1500,
            'payment_method' => 'mobile-banking',
            'transaction_id' => 'TXN-PARENT-001',
            'payment_status' => 'pending',
        ]);
    }

    public function test_transaction_id_is_required_for_non_cash_payment(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'card',
                'transaction_id' => '',
            ])
            ->assertSessionHasErrors('transaction_id');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_pending_booking_cannot_be_paid(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child, 'pending');

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'cash',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_parent_cannot_pay_another_parents_booking(): void
    {
        [$firstParent] = $this->createParentAndChild('First Child');
        [, $secondChild] = $this->createParentAndChild('Second Child');
        $booking = $this->createBooking($secondChild);

        $this->actingAs($firstParent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'cash',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_duplicate_pending_payment_is_not_created(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $this->createPayment($booking);

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'cash',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_admin_can_mark_pending_payment_as_paid(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $payment = $this->createPayment($booking);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.payments.mark-paid', $payment));

        $response->assertRedirect(
            route('admin.payments.show', $payment)
        );

        $payment->refresh();

        $this->assertSame('paid', $payment->payment_status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_parent_cannot_access_admin_payment_management(): void
    {
        [$parent] = $this->createParentAndChild();

        $this->actingAs($parent)
            ->get(route('admin.payments.index'))
            ->assertForbidden();
    }

    public function test_parent_can_retry_after_failed_payment(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $this->createPayment($booking, 'failed', 'FAILED-TXN-001');

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'transaction_id' => 'RETRY-TXN-002',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('payments', 2);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->booking_id,
            'transaction_id' => 'RETRY-TXN-002',
            'payment_status' => 'pending',
        ]);
    }

    public function test_parent_sees_payment_history(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->createPayment(
            $booking,
            'failed',
            'FAILED-HISTORY-001'
        );

        $this->createPayment(
            $booking,
            'pending',
            'PENDING-HISTORY-002'
        );

        $this->actingAs($parent)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Payment Information')
            ->assertSee('FAILED-HISTORY-001')
            ->assertSee('PENDING-HISTORY-002');
    }

    public function test_paid_booking_cannot_be_cancelled_directly(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $this->createPayment($booking, 'paid');

        $this->actingAs($parent)
            ->patch(route('bookings.cancel', $booking))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'confirmed',
        ]);
    }
}
