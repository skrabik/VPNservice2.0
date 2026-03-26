<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerAuthLink;
use Illuminate\Support\Facades\DB;

class CustomerAuthLinkService
{
    private const BROWSER_LOGIN_TTL_MINUTES = 15;

    private const TELEGRAM_LINK_TTL_MINUTES = 20;

    public function __construct(
        private readonly CustomerCabinetLinkService $cabinetLinkService = new CustomerCabinetLinkService,
    ) {}

    public function createBrowserLoginUrl(Customer $customer): string
    {
        $authLink = $this->issueLink(
            $customer,
            CustomerAuthLink::PURPOSE_BROWSER_LOGIN,
            now()->addMinutes(self::BROWSER_LOGIN_TTL_MINUTES),
        );

        return route('customer.telegram.browser-login', ['token' => $authLink->token], true);
    }

    public function createTelegramLinkUrl(Customer $customer): string
    {
        $authLink = $this->issueLink(
            $customer,
            CustomerAuthLink::PURPOSE_TELEGRAM_LINK,
            now()->addMinutes(self::TELEGRAM_LINK_TTL_MINUTES),
        );

        return $this->cabinetLinkService->getBotStartUrl('link_'.$authLink->token);
    }

    public function consumeBrowserLoginToken(string $token): ?Customer
    {
        return DB::transaction(function () use ($token) {
            $authLink = CustomerAuthLink::query()
                ->where('token', $token)
                ->where('purpose', CustomerAuthLink::PURPOSE_BROWSER_LOGIN)
                ->lockForUpdate()
                ->first();

            if (! $authLink || ! $authLink->isUsable()) {
                return null;
            }

            $authLink->forceFill([
                'used_at' => now(),
            ])->save();

            return $authLink->customer()->first();
        });
    }

    public function getValidTelegramLink(string $token): ?CustomerAuthLink
    {
        $authLink = CustomerAuthLink::query()
            ->where('token', $token)
            ->where('purpose', CustomerAuthLink::PURPOSE_TELEGRAM_LINK)
            ->first();

        if (! $authLink || ! $authLink->isUsable()) {
            return null;
        }

        return $authLink;
    }

    public function markAsUsed(CustomerAuthLink $authLink): void
    {
        $authLink->forceFill([
            'used_at' => now(),
        ])->save();
    }

    private function issueLink(Customer $customer, string $purpose, $expiresAt): CustomerAuthLink
    {
        CustomerAuthLink::query()
            ->where('customer_id', $customer->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->delete();

        return CustomerAuthLink::query()->create([
            'customer_id' => $customer->id,
            'purpose' => $purpose,
            'token' => $this->generateToken(),
            'expires_at' => $expiresAt,
        ]);
    }

    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    }
}
