<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Server;
use App\Models\Subscription;
use App\Models\VpnKey;
use App\Services\VpnProviders\VpnAccessManager;

class CustomerOnboardingService
{
    private const PROMO_PERIOD_DAYS = 15;

    public function __construct(
        private readonly VpnAccessManager $vpnAccessManager = new VpnAccessManager,
        private readonly CustomerInstructionService $instructionService = new CustomerInstructionService,
        private readonly CustomerCabinetLinkService $cabinetLinkService = new CustomerCabinetLinkService,
    ) {}

    public function createWelcomeSubscription(Customer $customer): Subscription
    {
        $promoPlan = Plan::updateOrCreate(
            ['slug' => 'promo'],
            [
                'title' => 'Промо план',
                'description' => 'Бесплатный план на 15 дней для новых пользователей',
                'price' => 0,
                'stars' => 1,
                'period' => self::PROMO_PERIOD_DAYS,
                'active' => true,
            ]
        );

        return Subscription::create([
            'customer_id' => $customer->id,
            'plan_id' => $promoPlan->id,
            'date_start' => now(),
            'date_end' => now()->addDays(self::PROMO_PERIOD_DAYS),
        ]);
    }

    public function createWelcomeVpnKey(Customer $customer): ?VpnKey
    {
        $server = Server::query()
            ->where('active', true)
            ->orderByDesc('id')
            ->first();

        if (! $server) {
            return null;
        }

        $activeSubscription = $customer->subscriptions()
            ->where('date_end', '>', now())
            ->latest('date_end')
            ->first();

        return $this->vpnAccessManager->createForCustomer($server, $customer, [
            'expires_at' => $activeSubscription?->date_end,
        ]);
    }

    public function getWelcomeAppLinks(): array
    {
        return $this->instructionService->getPlatforms();
    }

    public function getClaimUrl(Customer $customer): ?string
    {
        return $this->cabinetLinkService->getClaimUrl($customer);
    }
}
