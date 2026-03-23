<?php

namespace App\Services\Payments;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ProcessYooKassaWebhookService
{
    public function __construct(
        private readonly YooKassaPaymentService $yooKassaPaymentService
    ) {
    }

    public function process(array $notification): void
    {
        $event = (string) ($notification['event'] ?? '');
        $paymentId = (string) data_get($notification, 'object.id', '');

        if ($paymentId === '') {
            throw new RuntimeException('YooKassa webhook does not contain payment id.');
        }

        $paymentObject = $this->yooKassaPaymentService->getPayment($paymentId);
        $status = (string) ($paymentObject['status'] ?? '');

        match ($event) {
            'payment.succeeded' => $this->handleSucceeded($paymentObject),
            'payment.canceled' => $this->syncPaymentState($paymentObject, $status ?: Payment::STATUS_CANCELED),
            default => $this->syncPaymentState($paymentObject, $status ?: Payment::STATUS_PENDING),
        };
    }

    private function handleSucceeded(array $paymentObject): void
    {
        $paymentId = (string) ($paymentObject['id'] ?? '');
        $status = (string) ($paymentObject['status'] ?? '');

        if ($status !== Payment::STATUS_SUCCEEDED) {
            Log::warning('YooKassa webhook status mismatch detected', [
                'payment_id' => $paymentId,
                'status' => $status,
            ]);

            return;
        }

        $customer = $this->resolveCustomer($paymentObject);
        $plan = $this->resolvePlan($paymentObject);

        FinalizeSubscriptionPaymentService::process($customer, $plan, [
            'amount' => data_get($paymentObject, 'amount.value', $plan->price),
            'currency' => data_get($paymentObject, 'amount.currency', 'RUB'),
            'transaction_id' => $paymentId,
            'provider' => Payment::PROVIDER_YOOKASSA,
            'payment_method' => (string) data_get($paymentObject, 'payment_method.type', Payment::METHOD_YOOKASSA_REDIRECT),
            'status' => Payment::STATUS_SUCCEEDED,
            'external_payment_id' => $paymentId,
            'payload' => $paymentObject,
        ]);
    }

    private function syncPaymentState(array $paymentObject, string $status): void
    {
        $paymentId = (string) ($paymentObject['id'] ?? '');

        if ($paymentId === '') {
            return;
        }

        $payment = Payment::query()
            ->where('provider', Payment::PROVIDER_YOOKASSA)
            ->where('external_payment_id', $paymentId)
            ->first();

        $customer = $payment?->customer ?? $this->resolveCustomer($paymentObject, false);

        if (! $payment && ! $customer) {
            Log::warning('Skipping YooKassa payment sync because customer was not resolved', [
                'payment_id' => $paymentId,
                'status' => $status,
            ]);

            return;
        }

        $attributes = [
            'amount' => data_get($paymentObject, 'amount.value'),
            'currency' => data_get($paymentObject, 'amount.currency', 'RUB'),
            'transaction_id' => $paymentId,
            'provider' => Payment::PROVIDER_YOOKASSA,
            'payment_method' => (string) data_get($paymentObject, 'payment_method.type', Payment::METHOD_YOOKASSA_REDIRECT),
            'status' => $status,
            'external_payment_id' => $paymentId,
            'payload' => $paymentObject,
        ];

        if ($payment) {
            $payment->update($attributes);

            return;
        }

        $customer->payments()->create($attributes);
    }

    private function resolveCustomer(array $paymentObject, bool $failIfMissing = true): ?Customer
    {
        $customerId = (int) data_get($paymentObject, 'metadata.customer_id', 0);

        if ($customerId <= 0) {
            if ($failIfMissing) {
                throw new RuntimeException('YooKassa payment metadata does not contain customer_id.');
            }

            return null;
        }

        $customer = Customer::find($customerId);
        if ($customer || ! $failIfMissing) {
            return $customer;
        }

        throw new RuntimeException('Customer from YooKassa metadata was not found.');
    }

    private function resolvePlan(array $paymentObject): Plan
    {
        $planId = (int) data_get($paymentObject, 'metadata.plan_id', 0);
        $plan = $planId > 0 ? Plan::find($planId) : null;

        return $plan ?? Plan::resolveOrCreateDefaultMonthlyPlan();
    }
}
