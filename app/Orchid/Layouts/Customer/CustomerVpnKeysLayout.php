<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Customer;

use App\Models\VpnKey;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CustomerVpnKeysLayout extends Table
{
    /**
     * @var string
     */
    protected $target = 'vpnKeys';

    /**
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
            TD::make('id', 'ID')->render(fn (VpnKey $vpnKey) => $vpnKey->id),

            TD::make('server_id', 'Server')->render(function (VpnKey $vpnKey) {
                return $vpnKey->server?->hostname
                    ?: $vpnKey->server?->ip_address
                    ?: ($vpnKey->server_id ? '#'.$vpnKey->server_id : '-');
            }),

            TD::make('server_inbound_id', 'Inbound')->render(function (VpnKey $vpnKey) {
                return $vpnKey->inbound?->remark
                    ?: $vpnKey->inbound?->tag
                    ?: ($vpnKey->server_inbound_id ? '#'.$vpnKey->server_inbound_id : '-');
            }),

            TD::make('access_key', 'Access Key')->render(
                fn (VpnKey $vpnKey) => str($vpnKey->access_key)->limit(80)
            ),

            TD::make('external_email', 'External Email')->render(
                fn (VpnKey $vpnKey) => $vpnKey->external_email ?: '-'
            ),

            TD::make('is_active', 'Status')->render(
                fn (VpnKey $vpnKey) => $vpnKey->deleted_at
                    ? 'Deleted'
                    : ($vpnKey->is_active ? 'Active' : 'Inactive')
            ),

            TD::make('expires_at', 'Expires At')->render(
                fn (VpnKey $vpnKey) => $vpnKey->expires_at?->format('d.m.Y H:i') ?: '-'
            ),

            TD::make('created_at', 'Created At')->render(
                fn (VpnKey $vpnKey) => $vpnKey->created_at?->format('d.m.Y H:i') ?: '-'
            ),

            TD::make('deleted_at', 'Deleted At')->render(
                fn (VpnKey $vpnKey) => $vpnKey->deleted_at?->format('d.m.Y H:i') ?: '-'
            ),
        ];
    }
}
