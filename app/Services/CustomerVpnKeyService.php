<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Server;
use App\Models\Subscription;
use App\Models\VpnKey;
use App\Services\VpnProviders\VpnAccessManager;
use DomainException;
use Illuminate\Database\Eloquent\Collection;

class CustomerVpnKeyService
{
    public function __construct(
        private readonly VpnAccessManager $vpnAccessManager = new VpnAccessManager
    ) {}

    public function getAvailableServers(): Collection
    {
        return Server::query()
            ->where('active', true)
            ->orderBy('hostname')
            ->get();
    }

    public function getCurrentKey(Customer $customer): ?VpnKey
    {
        return $customer->activeVpnKeys()
            ->with('server')
            ->latest('id')
            ->first();
    }

    public function getActiveSubscription(Customer $customer): ?Subscription
    {
        return $customer->subscriptions()
            ->where('date_end', '>', now())
            ->latest('date_end')
            ->first();
    }

    public function ensureCanManageKeys(Customer $customer): void
    {
        if (! $this->getActiveSubscription($customer)) {
            throw new DomainException('У клиента нет активной подписки.');
        }
    }

    public function createKeyForServer(Customer $customer, Server $server): VpnKey
    {
        $subscription = $this->getActiveSubscription($customer);

        if (! $subscription) {
            throw new DomainException('Нельзя создать ключ без активной подписки.');
        }

        $server->loadMissing(['defaultInbound', 'inbounds']);

        $this->vpnAccessManager->deleteCustomerKeys($customer);

        return $this->vpnAccessManager->createForCustomer($server, $customer, [
            'expires_at' => $subscription->date_end,
        ]);
    }
}
