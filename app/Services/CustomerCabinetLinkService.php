<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\URL;

class CustomerCabinetLinkService
{
    public function shouldOfferClaimLink(Customer $customer): bool
    {
        return blank($customer->email) || blank($customer->password);
    }

    public function getClaimUrl(Customer $customer): ?string
    {
        if (! $this->shouldOfferClaimLink($customer)) {
            return null;
        }

        return URL::temporarySignedRoute(
            'customer.claim.create',
            now()->addDays(7),
            ['customer' => $customer]
        );
    }

    public function getBotUrl(): string
    {
        $botName = env('TELEGRAM_MAIN_BOT_NAME');

        if (filled($botName)) {
            return 'https://t.me/'.$botName;
        }

        return 'https://t.me/quantum_shield_bot';
    }
}
