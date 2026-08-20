<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SslCommerzPaymentTest extends TestCase
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

    public function test_payment_page_shows_sslcommerz_sandbox_checkout(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();

        $this->actingAs($parent)
            ->get(route('payments.create', $booking))
            ->assertOk()
            ->assertSee('Pay with SSLCOMMERZ')
            ->assertSee('SSLCOMMERZ Sandbox')
            ->assertSee('Continue to SSLCOMMERZ');
    }

    public function test_parent_is_redirected_to_sslcommerz_gateway(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();

        Http::fake([
            'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'sessionkey' => 'SSL-SESSION-001',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/gateway/test',
            ], 200),
        ]);

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'customer_phone' => '01811000000',
            ])
            ->assertRedirect('https://sandbox.sslcommerz.com/gateway/test');

        $payment = Payment::firstOrFail();

        $this->assertSame('pending', $payment->payment_status);
        $this->assertSame('sslcommerz', $payment->gateway_name);
        $this->assertSame('session_created', $payment->gateway_status);
        $this->assertSame('SSL-SESSION-001', $payment->gateway_session_key);

        Http::assertSent(function (Request $request) use ($booking, $payment) {
            return $request->url() === 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
                && $request['store_id'] === 'sandbox-store'
                && $request['total_amount'] === '1200.00'
                && $request['currency'] === 'BDT'
                && $request['tran_id'] === $payment->transaction_id
                && $request['product_name'] === $booking->service->name
                && $request['product_profile'] === 'non-physical-goods';
        });
    }

    public function test_success_callback_validates_and_marks_payment_paid(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();
        $payment = $this->createGatewayPayment($booking);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $payment->transaction_id,
                'val_id' => 'VAL-001',
                'amount' => '1200.00',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK-001',
                'card_type' => 'VISA-Brac bank',
                'risk_level' => '0',
            ], 200),
        ]);

        $this->actingAs($parent)
            ->post(route('sslcommerz.success'), [
                'tran_id' => $payment->transaction_id,
                'val_id' => 'VAL-001',
                'status' => 'VALID',
            ])
            ->assertRedirect(route('payments.show', $payment))
            ->assertSessionHas('success');

        $payment->refresh();

        $this->assertSame('paid', $payment->payment_status);
        $this->assertSame('valid', $payment->gateway_status);
        $this->assertSame('VAL-001', $payment->validation_id);
        $this->assertSame('BANK-001', $payment->bank_transaction_id);
        $this->assertSame('VISA-Brac bank', $payment->card_type);
        $this->assertSame('card', $payment->payment_method);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_validation_rejects_wrong_amount(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();
        $payment = $this->createGatewayPayment($booking);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $payment->transaction_id,
                'val_id' => 'VAL-WRONG',
                'amount' => '1.00',
                'currency' => 'BDT',
                'risk_level' => '0',
            ], 200),
        ]);

        $this->actingAs($parent)
            ->post(route('sslcommerz.success'), [
                'tran_id' => $payment->transaction_id,
                'val_id' => 'VAL-WRONG',
                'status' => 'VALID',
            ])
            ->assertSessionHas('error');

        $this->assertSame('failed', $payment->fresh()->payment_status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_cancel_callback_marks_pending_payment_failed_for_retry(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();
        $payment = $this->createGatewayPayment($booking);

        $this->actingAs($parent)
            ->post(route('sslcommerz.cancel'), [
                'tran_id' => $payment->transaction_id,
            ])
            ->assertRedirect(route('payments.show', $payment));

        $payment->refresh();

        $this->assertSame('failed', $payment->payment_status);
        $this->assertSame('cancelled', $payment->gateway_status);
    }

    public function test_ipn_can_verify_payment_without_parent_session(): void
    {
        [, $booking] = $this->createConfirmedBooking();
        $payment = $this->createGatewayPayment($booking);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALIDATED',
                'tran_id' => $payment->transaction_id,
                'val_id' => 'VAL-IPN',
                'amount' => '1200.00',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK-IPN',
                'card_type' => 'BKASH-Bkash',
                'risk_level' => '0',
            ], 200),
        ]);

        $this->post(route('sslcommerz.ipn'), [
            'tran_id' => $payment->transaction_id,
            'val_id' => 'VAL-IPN',
            'status' => 'VALID',
        ])->assertOk();

        $payment->refresh();

        $this->assertSame('paid', $payment->payment_status);
        $this->assertSame('mobile-banking', $payment->payment_method);
        $this->assertSame('validated', $payment->gateway_status);
    }

    public function test_parent_can_check_gateway_status_for_pending_payment(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();
        $payment = $this->createGatewayPayment($booking);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php*' => Http::response([
                'APIConnect' => 'DONE',
                'element' => [[
                    'status' => 'VALID',
                    'tran_id' => $payment->transaction_id,
                    'val_id' => 'VAL-CHECK',
                    'amount' => '1200.00',
                    'currency_type' => 'BDT',
                    'bank_tran_id' => 'BANK-CHECK',
                    'card_type' => 'MASTER-City Bank',
                    'risk_level' => '0',
                ]],
            ], 200),
        ]);

        $this->actingAs($parent)
            ->post(route('payments.check-status', $payment))
            ->assertSessionHas('success');

        $payment->refresh();

        $this->assertSame('paid', $payment->payment_status);
        $this->assertSame('VAL-CHECK', $payment->validation_id);
        $this->assertSame('BANK-CHECK', $payment->bank_transaction_id);
    }

    public function test_sslcommerz_payment_cannot_be_manually_marked_paid_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $booking] = $this->createConfirmedBooking();
        $payment = $this->createGatewayPayment($booking);

        $this->actingAs($admin)
            ->post(route('admin.payments.mark-paid', $payment))
            ->assertSessionHas('error');

        $this->assertSame('pending', $payment->fresh()->payment_status);
    }

    public function test_admin_can_initiate_and_confirm_sslcommerz_refund(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $booking] = $this->createConfirmedBooking();
        $booking->update(['status' => 'cancelled']);

        $payment = $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => 'card',
            'gateway_name' => 'sslcommerz',
            'gateway_status' => 'valid',
            'transaction_id' => 'LN-REFUND-001',
            'bank_transaction_id' => 'BANK-REFUND-001',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php*' => Http::sequence()
                ->push([
                    'APIConnect' => 'DONE',
                    'bank_tran_id' => 'BANK-REFUND-001',
                    'trans_id' => 'LN-REFUND-001',
                    'refund_ref_id' => 'REF-001',
                    'status' => 'success',
                ], 200)
                ->push([
                    'APIConnect' => 'DONE',
                    'refund_ref_id' => 'REF-001',
                    'status' => 'refunded',
                ], 200),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.refund', $payment), [
                'refund_note' => 'Approved cancellation refund.',
            ])
            ->assertSessionHas('success');

        $payment->refresh();

        $this->assertSame('REF-001', $payment->refund_reference);
        $this->assertSame('success', $payment->refund_gateway_status);
        $this->assertNull($payment->refunded_at);

        $this->actingAs($admin)
            ->post(route('admin.payments.refund-status', $payment))
            ->assertSessionHas('success');

        $payment->refresh();

        $this->assertSame('refunded', $payment->refund_gateway_status);
        $this->assertNotNull($payment->refunded_at);
    }

    public function test_missing_gateway_credentials_are_handled_without_creating_payment(): void
    {
        config([
            'sslcommerz.store_id' => null,
            'sslcommerz.store_password' => null,
        ]);

        [$parent, $booking] = $this->createConfirmedBooking();

        $this->actingAs($parent)
            ->post(route('payments.store', $booking), [
                'customer_phone' => '01811000000',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
    }

    private function createConfirmedBooking(): array
    {
        $parent = User::factory()->create([
            'role' => 'parent',
            'phone' => '01811000000',
        ]);

        $profile = $parent->parentProfile()->create([
            'address' => 'Dhanmondi, Dhaka',
        ]);

        $child = $profile->children()->create([
            'full_name' => 'Gateway Payment Child',
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Gateway Payment Service ' . uniqid(),
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

    private function createGatewayPayment($booking): Payment
    {
        return $booking->payments()->create([
            'amount' => $booking->total_amount,
            'payment_method' => 'card',
            'gateway_name' => 'sslcommerz',
            'gateway_status' => 'session_created',
            'gateway_session_key' => 'SESSION-TEST',
            'transaction_id' => 'LNSSL' . uniqid(),
            'payment_status' => 'pending',
        ]);
    }
}
