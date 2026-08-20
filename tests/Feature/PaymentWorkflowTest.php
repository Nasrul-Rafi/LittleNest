<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Child;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'sslcommerz.sandbox' => true,
            'sslcommerz.base_url' => 'https://sandbox.sslcommerz.com',
            'sslcommerz.store_id' => 'sandbox-store',
            'sslcommerz.store_password' => 'sandbox-password',
        ]);
    }

    private function createParentAndChild(
        string $childName = 'Payment Child'
    ): array {
        $parent = User::factory()->create([
            'role' => 'parent',
            'phone' => '01711000000',
        ]);

        $parentProfile = $parent->parentProfile()->create([
            'address' => 'Dhanmondi, Dhaka',
        ]);

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
        ?string $transactionId = 'TXN-EXISTING-001'
    ): Payment {
        return $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => 'mobile-banking',
            'transaction_id' => $transactionId,
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);
    }

    private function fakeSession(): void
    {
        Http::fake([
            'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'sessionkey' => 'SESSION-123',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/test-checkout',
            ], 200),
        ]);
    }

    public function test_parent_can_start_sslcommerz_payment_for_confirmed_booking(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $this->fakeSession();

        $response = $this
            ->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'customer_phone' => '01711000000',
                'amount' => 1,
                'payment_status' => 'paid',
            ]);

        $response->assertRedirect('https://sandbox.sslcommerz.com/test-checkout');

        $payment = Payment::firstOrFail();

        $this->assertSame('1500.00', $payment->amount);
        $this->assertSame('sslcommerz', $payment->gateway_name);
        $this->assertSame('pending', $payment->payment_status);
        $this->assertSame('session_created', $payment->gateway_status);
        $this->assertSame('SESSION-123', $payment->gateway_session_key);
        $this->assertNotNull($payment->transaction_id);
    }

    public function test_sslcommerz_payment_requires_valid_mobile_number(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'customer_phone' => '12345',
            ])
            ->assertSessionHasErrors('customer_phone');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_pending_booking_cannot_be_paid(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child, 'pending');

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'customer_phone' => '01711000000',
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
                'customer_phone' => '01711000000',
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
                'customer_phone' => '01711000000',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_admin_can_mark_legacy_pending_payment_as_paid(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $payment = $this->createPayment(
            $booking,
            'pending',
            'TXN-PENDING-001'
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
        $this->createPayment($booking, 'failed', 'TXN-FAILED-001');
        $this->fakeSession();

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'customer_phone' => '01711000000',
            ])
            ->assertRedirect('https://sandbox.sslcommerz.com/test-checkout');

        $this->assertDatabaseCount('payments', 2);

        $latestPayment = Payment::latest('payment_id')->firstOrFail();

        $this->assertSame('pending', $latestPayment->payment_status);
        $this->assertSame('sslcommerz', $latestPayment->gateway_name);
    }

    public function test_parent_sees_payment_history(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->createPayment(
            $booking,
            'failed',
            'TXN-FAILED-HISTORY-001'
        );

        $this->actingAs($parent)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Payment Information')
            ->assertSee('TXN-FAILED-HISTORY-001');
    }

    public function test_parent_can_view_receipt_after_paid_payment(): void
    {
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $payment = $this->createPayment($booking);

        $this->actingAs($parent)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Payment Receipt');
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
