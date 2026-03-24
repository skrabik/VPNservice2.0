<?php

namespace App\Services\Payments;

use App\Models\Customer;
use App\Models\Plan;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class YooKassaPaymentService
{
    public function createHostedPayment(Customer $customer, Plan $plan, ?string $returnUrl = null): array
    {
        $amount = number_format((float) $plan->price, 2, '.', '');

        if ((float) $amount <= 0) {
            throw new RuntimeException('YooKassa payment amount must be greater than zero.');
        }

        $response = $this->httpClient()
            ->withHeaders([
                'Idempotence-Key' => (string) Str::uuid(),
            ])
            ->post('/payments', [
                'amount' => [
                    'value' => $amount,
                    'currency' => 'RUB',
                ],
                'capture' => true,
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => $returnUrl ?: (string) config('yookassa.return_url'),
                ],
                'description' => sprintf('Подписка "%s" на %d дней', $plan->title, $plan->period),
                'metadata' => [
                    'customer_id' => (string) $customer->id,
                    'plan_id' => (string) $plan->id,
                    'telegram_id' => (string) $customer->telegram_id,
                ],
            ])
            ->throw()
            ->json();

        if (! is_string(data_get($response, 'confirmation.confirmation_url'))) {
            throw new RuntimeException('YooKassa did not return a confirmation URL.');
        }

        return $response;
    }

    public function getPayment(string $paymentId): array
    {
        return $this->httpClient()
            ->get('/payments/'.$paymentId)
            ->throw()
            ->json();
    }

    private function httpClient(): PendingRequest
    {
        if (! config('yookassa.enabled')) {
            throw new RuntimeException('YooKassa integration is disabled.');
        }

        $shopId = (string) config('yookassa.shop_id');
        $secretKey = (string) config('yookassa.secret_key');

        if ($shopId === '' || $secretKey === '') {
            throw new RuntimeException('YooKassa credentials are not configured.');
        }

        return Http::baseUrl((string) config('yookassa.base_url'))
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($shopId, $secretKey);
    }
}
