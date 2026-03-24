<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\CustomerCabinetLinkService;
use App\Services\CustomerInstructionService;
use App\Services\CustomerStatusService;
use App\Services\Payments\FinalizeSubscriptionPaymentService;
use App\Services\Payments\YooKassaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function __construct(
        private readonly CustomerStatusService $statusService,
        private readonly CustomerInstructionService $instructionService,
        private readonly CustomerCabinetLinkService $cabinetLinkService,
        private readonly YooKassaPaymentService $yooKassaPaymentService,
    ) {}

    public function dashboard(Request $request): View
    {
        $customer = $request->user('customer');
        $overview = $this->statusService->getOverview($customer);

        return view('customer.dashboard', [
            'customer' => $customer,
            'overview' => $overview,
            'botUrl' => $this->cabinetLinkService->getBotUrl(),
        ]);
    }

    public function status(Request $request): View
    {
        return view('customer.status', [
            'overview' => $this->statusService->getOverview($request->user('customer')),
        ]);
    }

    public function instructions(Request $request): View
    {
        $selectedType = $this->instructionService->normalizeType($request->query('type'));

        return view('customer.instructions', [
            'selectedType' => $selectedType,
            'platforms' => $this->instructionService->getPlatforms(),
            'instructionHtml' => nl2br($this->instructionService->getInstructions($selectedType)),
        ]);
    }

    public function pay(Request $request): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $plan = Plan::resolveOrCreateDefaultMonthlyPlan();

        $latestYooKassaPayment = $customer->payments()
            ->where('provider', Payment::PROVIDER_YOOKASSA)
            ->latest('id')
            ->first();

        $shouldRefreshPayment = $request->query('from') === 'yookassa' || $request->boolean('refresh');

        if ($shouldRefreshPayment && $latestYooKassaPayment && $latestYooKassaPayment->status === Payment::STATUS_PENDING) {
            try {
                $latestYooKassaPayment = $this->refreshYooKassaPaymentState($customer, $latestYooKassaPayment);
            } catch (Throwable $exception) {
                Log::warning('Failed to refresh YooKassa payment state in customer cabinet', [
                    'customer_id' => $customer->id,
                    'payment_id' => $latestYooKassaPayment->id,
                    'external_payment_id' => $latestYooKassaPayment->external_payment_id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return view('customer.pay', [
            'customer' => $customer,
            'plan' => $plan,
            'activeSubscription' => $customer->getActiveSubscription(),
            'latestYooKassaPayment' => $latestYooKassaPayment,
            'yooKassaEnabled' => (bool) config('yookassa.enabled'),
            'botUrl' => $this->cabinetLinkService->getBotUrl(),
        ]);
    }

    public function createYooKassaPayment(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $plan = Plan::resolveOrCreateDefaultMonthlyPlan();

        try {
            $payment = $this->yooKassaPaymentService->createHostedPayment(
                $customer,
                $plan,
                route('customer.pay', ['from' => 'yookassa'])
            );

            $paymentId = (string) ($payment['id'] ?? '');
            $confirmationUrl = (string) data_get($payment, 'confirmation.confirmation_url', '');

            if ($paymentId === '' || $confirmationUrl === '') {
                throw new \RuntimeException('YooKassa payment response is incomplete.');
            }

            Payment::query()->updateOrCreate(
                [
                    'provider' => Payment::PROVIDER_YOOKASSA,
                    'external_payment_id' => $paymentId,
                ],
                [
                    'customer_id' => $customer->id,
                    'subscription_id' => null,
                    'amount' => data_get($payment, 'amount.value', $plan->price),
                    'currency' => data_get($payment, 'amount.currency', 'RUB'),
                    'transaction_id' => $paymentId,
                    'payment_method' => (string) data_get($payment, 'payment_method.type', Payment::METHOD_YOOKASSA_REDIRECT),
                    'status' => (string) data_get($payment, 'status', Payment::STATUS_PENDING),
                    'payload' => $payment,
                ]
            );

            return redirect()->away($confirmationUrl);
        } catch (Throwable $exception) {
            Log::error('Failed to create YooKassa payment from customer cabinet', [
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'payment' => 'Не удалось создать ссылку на оплату через YooKassa. Попробуйте позже.',
            ]);
        }
    }

    private function refreshYooKassaPaymentState(Customer $customer, Payment $payment): Payment
    {
        $paymentObject = $this->yooKassaPaymentService->getPayment((string) $payment->external_payment_id);
        $status = (string) data_get($paymentObject, 'status', Payment::STATUS_PENDING);
        $paymentId = (string) data_get($paymentObject, 'id', $payment->external_payment_id);

        if ($status === Payment::STATUS_SUCCEEDED) {
            $planId = (int) data_get($paymentObject, 'metadata.plan_id', 0);
            $plan = $planId > 0 ? Plan::query()->find($planId) : null;
            $plan = $plan ?? Plan::resolveOrCreateDefaultMonthlyPlan();

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
        } else {
            $payment->update([
                'amount' => data_get($paymentObject, 'amount.value', $payment->amount),
                'currency' => data_get($paymentObject, 'amount.currency', $payment->currency ?: 'RUB'),
                'transaction_id' => $paymentId,
                'payment_method' => (string) data_get($paymentObject, 'payment_method.type', Payment::METHOD_YOOKASSA_REDIRECT),
                'status' => $status,
                'payload' => $paymentObject,
            ]);
        }

        return $payment->fresh();
    }
}
