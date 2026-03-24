<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
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
            [$customer, $requiresOnboarding] = $this->resolveCustomer($telegramUser);

            if ($requiresOnboarding) {
                $this->onboardingService->createWelcomeSubscription($customer);
                $this->onboardingService->createWelcomeVpnKey($customer);
            }

            return $customer;
        });
    }

    /**
     * @param  array<string, mixed>  $telegramUser
     * @return array{0: Customer, 1: bool}
     */
    private function resolveCustomer(array $telegramUser): array
    {
        $telegramId = (string) $telegramUser['id'];
        $attributes = [
            'telegram_username' => $telegramUser['username'] ?? null,
            'first_name' => $telegramUser['first_name'] ?? 'Telegram',
            'last_name' => $telegramUser['last_name'] ?? null,
        ];

        $customer = Customer::withTrashed()
            ->where('telegram_id', $telegramId)
            ->first();

        if ($customer) {
            $requiresOnboarding = $customer->trashed();

            $customer->forceFill($attributes);

            if ($requiresOnboarding) {
                $customer->restore();
            }

            $customer->save();

            return [$customer, $requiresOnboarding];
        }

        try {
            $customer = Customer::query()->create([
                'telegram_id' => $telegramId,
                ...$attributes,
            ]);

            return [$customer, true];
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23505') {
                throw $exception;
            }

            $customer = Customer::withTrashed()
                ->where('telegram_id', $telegramId)
                ->firstOrFail();

            $requiresOnboarding = $customer->trashed();

            $customer->forceFill($attributes);

            if ($requiresOnboarding) {
                $customer->restore();
            }

            $customer->save();

            return [$customer, $requiresOnboarding];
        }
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
