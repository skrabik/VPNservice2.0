<?php

namespace App\Services\VpnProviders;

use App\Models\Customer;
use App\Models\Server;
use App\Models\VpnKey;

interface VpnProvisioningServiceInterface
{
    public function createAccess(Server $server, Customer $customer, array $context = []): array;

    public function deleteAccess(Server $server, VpnKey $vpnKey): bool;
}
