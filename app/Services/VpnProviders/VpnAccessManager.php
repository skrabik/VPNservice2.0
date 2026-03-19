<?php

namespace App\Services\VpnProviders;

use App\Models\Customer;
use App\Models\Server;
use App\Models\VpnKey;

class VpnAccessManager
{
    public function __construct(
        private readonly VpnProviderFactory $providerFactory = new VpnProviderFactory
    ) {}

    public function createForCustomer(Server $server, Customer $customer, array $context = []): VpnKey
    {
        $provider = $this->providerFactory->forServer($server);
        $attributes = $provider->createAccess($server, $customer, $context);
        $attributes['server_id'] = $server->id;

        return $customer->vpnKeys()->create($attributes);
    }

    public function deleteCustomerKeys(Customer $customer): void
    {
        $keys = $customer->vpnKeys()->with(['server', 'inbound'])->get();

        foreach ($keys as $key) {
            $server = $key->server;

            if ($server) {
                $provider = $this->providerFactory->forServer($server);
                $provider->deleteAccess($server, $key);
            }

            $key->delete();
        }
    }
}
