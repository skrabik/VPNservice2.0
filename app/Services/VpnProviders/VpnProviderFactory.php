<?php

namespace App\Services\VpnProviders;

use App\Models\Server;

class VpnProviderFactory
{
    public function forServer(Server $server): VpnProvisioningServiceInterface
    {
        return new ThreeXuiVpnProvisioningService;
    }
}
