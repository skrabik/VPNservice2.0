<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerTelegramAuthService
{
    public function __construct(
        private readonly CustomerOnboardingService $onboardingService = new CustomerOnboardingService,
    ) {}

    /**
     * @throws ValidationException
     */
    public function authenticateByInitData(string $initData): Customer
    {
        $payload = $this->validateInitData($initData);
        $telegramUser = $payload['user'];

        return DB::transaction(function () use ($telegramUser) {
            $customer = Customer::query()
                ->where('telegram_id', (string) $telegramUser['id'])
                ->first();

            if ($customer) {
                $customer->forceFill([
                    'telegram_username' => $telegramUser['username'] ?? $customer->telegram_username,
                    'first_name' => $telegramUser['first_name'] ?? $customer->first_name,
                    'last_name' => $telegramUser['last_name'] ?? $customer->last_name,
                ])->save();

                return $customer;
            }

            $customer = Customer::query()->create([
                'telegram_id' => (string) $telegramUser['id'],
                'telegram_username' => $telegramUser['username'] ?? null,
                'first_name' => $telegramUser['first_name'] ?? 'Telegram',
                'last_name' => $telegramUser['last_name'] ?? null,
            ]);

            $this->onboardingService->createWelcomeSubscription($customer);
            $this->onboardingService->createWelcomeVpnKey($customer);

            return $customer;
        });
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validateInitData(string $initData): array
    {
        parse_str($initData, $data);

        if (! is_array($data) || empty($data['hash']) || empty($data['auth_date']) || empty($data['user'])) {
            throw ValidationException::withMessages([
                'telegram' => 'Telegram не передал корректные данные авторизации.',
            ]);
        }

        $botToken = (string) config('telegram.bots.main.token');

        if ($botToken === '') {
            throw ValidationException::withMessages([
                'telegram' => 'Не настроен токен Telegram-бота для авторизации.',
            ]);
        }

        $receivedHash = (string) $data['hash'];
        unset($data['hash']);
        ksort($data);

        $dataCheckString = collect($data)
            ->map(fn ($value, $key) => $key.'='.$value)
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (! hash_equals($calculatedHash, $receivedHash)) {
            throw ValidationException::withMessages([
                'telegram' => 'Не удалось подтвердить Telegram-сессию.',
            ]);
        }

        if ((int) $data['auth_date'] < now()->subDay()->timestamp) {
            throw ValidationException::withMessages([
                'telegram' => 'Сессия Telegram устарела. Откройте кабинет заново из бота.',
            ]);
        }

        $user = json_decode((string) $data['user'], true);

        if (! is_array($user) || empty($user['id'])) {
            throw ValidationException::withMessages([
                'telegram' => 'Telegram не передал данные пользователя.',
            ]);
        }

        $data['user'] = $user;

        return $data;
    }
}
