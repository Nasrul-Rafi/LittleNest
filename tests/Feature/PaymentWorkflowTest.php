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
            'name' => 'Payment Test Service ' . uniqid(),
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
        string $status = 'paid',
        ?string $transactionId = 'SIM-EXISTING-001'
    ): Payment {
        return $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => 'mobile-banking',
            'transaction_id' => $transactionId,
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);
    }

    public function test_parent_can_complete_simulated_payment_for_confirmed_booking(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $response = $this
            ->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01711000000',
                'demo_confirmation' => '1',
                'amount' => 1,
                'payment_status' => 'failed',
            ]);

        $payment = Payment::first();

        $this->assertNotNull($payment);
        $response->assertRedirect(route('payments.show', $payment));
        $response->assertSessionHas('success');

        $this->assertSame('1500.00', $payment->amount);
        $this->assertSame('mobile-banking', $payment->payment_method);
        $this->assertSame('paid', $payment->payment_status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame(
            'SIM-LN-' . str_pad((string) $payment->payment_id, 6, '0', STR_PAD_LEFT),
            $payment->transaction_id
        );
    }

    public function test_simulated_payment_requires_valid_mobile_number(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '12345',
                'demo_confirmation' => '1',
            ])
            ->assertSessionHasErrors('mobile_number');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_simulated_payment_requires_demo_confirmation(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01711000000',
            ])
            ->assertSessionHasErrors('demo_confirmation');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_pending_booking_cannot_be_paid(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child, 'pending');

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01711000000',
                'demo_confirmation' => '1',
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
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01711000000',
                'demo_confirmation' => '1',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_duplicate_active_payment_is_not_created(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $this->createPayment($booking);

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01711000000',
                'demo_confirmation' => '1',
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
        $payment = $this->createPayment(
            $booking,
            'pending',
            'SIM-PENDING-001'
        );

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
        $this->createPayment($booking, 'failed', 'SIM-FAILED-001');

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01711000000',
                'demo_confirmation' => '1',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('payments', 2);

        $latestPayment = Payment::latest('payment_id')->first();

        $this->assertSame('paid', $latestPayment->payment_status);
        $this->assertStringStartsWith('SIM-LN-', $latestPayment->transaction_id);
    }

    public function test_parent_sees_payment_history(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->createPayment(
            $booking,
            'failed',
            'SIM-FAILED-HISTORY-001'
        );

        $this->actingAs($parent)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Payment Information')
            ->assertSee('SIM-FAILED-HISTORY-001');
    }

    public function test_parent_can_view_receipt_after_simulated_payment(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01711000000',
                'demo_confirmation' => '1',
            ]);

        $payment = Payment::first();

        $this->actingAs($parent)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Payment Receipt')
            ->assertSee('Mobile Banking (Demo)');
    }

    public function test_paid_booking_cannot_be_cancelled_directly(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $this->createPayment($booking);

        $this->actingAs($parent)
            ->patch(route('bookings.cancel', $booking))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'confirmed',
        ]);
    }
}
