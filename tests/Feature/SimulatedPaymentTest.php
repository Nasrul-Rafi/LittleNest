<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulatedPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_payment_page_shows_demo_payment_notice(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();

        $this->actingAs($parent)
            ->get(route('payments.create', $booking))
            ->assertOk()
            ->assertSee('Demo Payment')
            ->assertSee('No real money will be charged')
            ->assertSee('Complete Demo Payment');
    }

    public function test_simulated_payment_generates_transaction_id_and_marks_payment_paid(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01811000000',
                'demo_confirmation' => '1',
            ])
            ->assertSessionHas('success');

        $payment = Payment::firstOrFail();

        $this->assertSame('paid', $payment->payment_status);
        $this->assertNotNull($payment->paid_at);
        $this->assertStringStartsWith('SIM-LN-', $payment->transaction_id);
    }

    public function test_simulated_payment_does_not_trust_amount_or_status_from_request(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'payment_method' => 'mobile-banking',
                'mobile_number' => '01911000000',
                'demo_confirmation' => '1',
                'amount' => 1,
                'payment_status' => 'failed',
            ]);

        $payment = Payment::firstOrFail();

        $this->assertSame('1200.00', $payment->amount);
        $this->assertSame('paid', $payment->payment_status);
    }

    private function createConfirmedBooking(): array
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $profile = $parent->parentProfile()->create();

        $child = $profile->children()->create([
            'full_name' => 'Demo Payment Child',
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Demo Payment Service ' . uniqid(),
            'price' => 1200,
            'duration_minutes' => 180,
            'status' => 'active',
        ]);

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '09:00',
            'status' => 'confirmed',
            'total_amount' => 1200,
        ]);

        return [$parent, $booking];
    }
}
