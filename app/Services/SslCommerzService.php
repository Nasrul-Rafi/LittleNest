<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SslCommerzService
{
    public function isConfigured(): bool
    {
        return filled(config('sslcommerz.store_id'))
            && filled(config('sslcommerz.store_password'));
    }

    public function createSession(
        Payment $payment,
        Booking $booking,
        User $parent,
        string $customerPhone
    ): array {
        if (!$this->isConfigured()) {
            throw new RuntimeException('SSLCOMMERZ credentials are not configured.');
        }

        $parentProfile = $parent->parentProfile;
        $address = trim((string) ($parentProfile?->address ?: 'Dhaka'));

        $payload = [
            'store_id' => config('sslcommerz.store_id'),
            'store_passwd' => config('sslcommerz.store_password'),
            'total_amount' => number_format((float) $payment->amount, 2, '.', ''),
            'currency' => 'BDT',
            'tran_id' => $payment->transaction_id,
            'success_url' => route('sslcommerz.success'),
            'fail_url' => route('sslcommerz.fail'),
            'cancel_url' => route('sslcommerz.cancel'),
            'cus_name' => $parent->name,
            'cus_email' => $parent->email,
            'cus_add1' => substr($address, 0, 50),
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $customerPhone,
            'shipping_method' => 'NO',
            'product_name' => substr($booking->service->name, 0, 255),
            'product_category' => 'Child Care',
            'product_profile' => 'non-physical-goods',
            'value_a' => (string) $payment->payment_id,
            'value_b' => $booking->display_reference,
        ];

        if ($this->canReceiveIpn()) {
            $payload['ipn_url'] = route('sslcommerz.ipn');
        }

        $response = Http::asForm()
            ->timeout((int) config('sslcommerz.timeout', 20))
            ->post($this->url('/gwprocess/v4/api.php'), $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Could not connect to SSLCOMMERZ.');
        }

        $data = $response->json();

        if (
            !is_array($data)
            || ($data['status'] ?? null) !== 'SUCCESS'
            || empty($data['GatewayPageURL'])
        ) {
            throw new RuntimeException(
                (string) ($data['failedreason'] ?? 'SSLCOMMERZ did not create a payment session.')
            );
        }

        return $data;
    }

    public function validate(string $validationId): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('SSLCOMMERZ credentials are not configured.');
        }

        $response = Http::timeout((int) config('sslcommerz.timeout', 20))
            ->get($this->url('/validator/api/validationserverAPI.php'), [
                'val_id' => $validationId,
                'store_id' => config('sslcommerz.store_id'),
                'store_passwd' => config('sslcommerz.store_password'),
                'format' => 'json',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Could not validate the SSLCOMMERZ payment.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('SSLCOMMERZ returned an invalid validation response.');
        }

        return $data;
    }

    public function queryTransaction(string $transactionId): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('SSLCOMMERZ credentials are not configured.');
        }

        $response = Http::timeout((int) config('sslcommerz.timeout', 20))
            ->get($this->url('/validator/api/merchantTransIDvalidationAPI.php'), [
                'tran_id' => $transactionId,
                'store_id' => config('sslcommerz.store_id'),
                'store_passwd' => config('sslcommerz.store_password'),
                'format' => 'json',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Could not check the SSLCOMMERZ transaction status.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('SSLCOMMERZ returned an invalid transaction status response.');
        }

        return $data;
    }

    public function latestTransaction(array $data): ?array
    {
        $elements = $data['element'] ?? null;

        if (!is_array($elements) || $elements === []) {
            return null;
        }

        foreach ($elements as $element) {
            if (is_array($element) && in_array(
                strtoupper((string) ($element['status'] ?? '')),
                ['VALID', 'VALIDATED'],
                true
            )) {
                return $element;
            }
        }

        return is_array($elements[0] ?? null) ? $elements[0] : null;
    }

    public function initiateRefund(Payment $payment, string $note): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('SSLCOMMERZ credentials are not configured.');
        }

        if (!$payment->bank_transaction_id) {
            throw new RuntimeException('Bank transaction ID is missing for this SSLCOMMERZ payment.');
        }

        $refundTransactionId = 'RF'
            . $payment->payment_id
            . now()->format('ymdHis');

        $response = Http::timeout((int) config('sslcommerz.timeout', 20))
            ->get($this->url('/validator/api/merchantTransIDvalidationAPI.php'), [
                'bank_tran_id' => $payment->bank_transaction_id,
                'refund_trans_id' => $refundTransactionId,
                'refund_amount' => number_format((float) $payment->amount, 2, '.', ''),
                'refund_remarks' => $note,
                'refe_id' => 'PAY-' . $payment->payment_id,
                'store_id' => config('sslcommerz.store_id'),
                'store_passwd' => config('sslcommerz.store_password'),
                'format' => 'json',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Could not send the refund request to SSLCOMMERZ.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('SSLCOMMERZ returned an invalid refund response.');
        }

        return $data;
    }

    public function queryRefund(string $refundReference): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('SSLCOMMERZ credentials are not configured.');
        }

        $response = Http::timeout((int) config('sslcommerz.timeout', 20))
            ->get($this->url('/validator/api/merchantTransIDvalidationAPI.php'), [
                'refund_ref_id' => $refundReference,
                'store_id' => config('sslcommerz.store_id'),
                'store_passwd' => config('sslcommerz.store_password'),
                'format' => 'json',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Could not check the SSLCOMMERZ refund status.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('SSLCOMMERZ returned an invalid refund status response.');
        }

        return $data;
    }

    public function validationMatches(Payment $payment, array $data): bool
    {
        $status = strtoupper((string) ($data['status'] ?? ''));
        $transactionMatches = hash_equals(
            (string) $payment->transaction_id,
            (string) ($data['tran_id'] ?? '')
        );
        $expectedAmount = number_format((float) $payment->amount, 2, '.', '');
        $receivedAmount = number_format((float) ($data['amount'] ?? 0), 2, '.', '');
        $currencyMatches = strtoupper((string) ($data['currency'] ?? $data['currency_type'] ?? 'BDT')) === 'BDT';
        $riskLevel = (string) ($data['risk_level'] ?? '0');

        return in_array($status, ['VALID', 'VALIDATED'], true)
            && $transactionMatches
            && $expectedAmount === $receivedAmount
            && $currencyMatches
            && $riskLevel !== '1';
    }

    private function canReceiveIpn(): bool
    {
        $host = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        return $host !== ''
            && $host !== 'localhost'
            && $host !== '127.0.0.1'
            && $host !== '::1';
    }

    private function url(string $path): string
    {
        return rtrim((string) config('sslcommerz.base_url'), '/') . $path;
    }
}
