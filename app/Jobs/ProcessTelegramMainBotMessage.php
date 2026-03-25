<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\CustomerPendingAction;
use App\Services\CustomerCabinetLinkService;
use App\Services\CustomerOnboardingService;
use App\Telegram\Services\CommandService;
use App\Telegram\Services\PreCheckoutQueryService;
use App\Telegram\Services\SuccessfulPaymentService;
use App\Telegram\Services\SupportTicketService;
use App\Telegram\TelegramManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class ProcessTelegramMainBotMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $jsonData;

    public function __construct(array $json)
    {
        $this->jsonData = $json;
    }

    public function handle(): void
    {
        try {
            $update = new Update($this->jsonData);
            $callbackQuery = $update->getCallbackQuery();
            $preCheckoutQuery = $update->getPreCheckoutQuery();
            $message = $update->getMessage();

            if (! $message && ! $callbackQuery && ! $preCheckoutQuery) {
                Log::warning('Unsupported Telegram update received', [
                    'update_keys' => array_keys($this->jsonData),
                ]);

                return;
            }

            $telegram_id = TelegramManager::extractTelegramId($update);

            if (! $telegram_id) {
                Log::warning('No telegram_id found in update');

                return;
            }

            $from = $callbackQuery?->getFrom() ?? $preCheckoutQuery?->getFrom() ?? $message?->getFrom();
            if (! $from) {
                Log::warning('No from payload found in update', [
                    'telegram_id' => $telegram_id,
                ]);

                return;
            }

            [$customer, $requiresOnboarding] = $this->resolveCustomer(
                (string) $telegram_id,
                $from->getUsername(),
                $from->getFirstName(),
                $from->getLastName(),
            );

            if ($requiresOnboarding) {
                $this->createWelcomeSubscription($customer);

                $this->createWelcomeVpnKey($customer);

                return;
            }

            if ($preCheckoutQuery) {
                PreCheckoutQueryService::process($update, $customer);

                return;
            }

            if ($message?->getSuccessfulPayment()) {
                SuccessfulPaymentService::process($update, $customer);

                return;
            }

            if ($message && $customer->pending_actions()->exists()) {
                $pendingAction = $customer->pending_actions()->first();

                if ($pendingAction->action_id === CustomerPendingAction::ACTION_SUPPORT_TICKET_TYPE &&
                    $message->getText() != '❌ Отмена'
                ) {
                    SupportTicketService::process($update, $customer);
                    $customer->pending_actions()->delete();

                    return;
                }

                $customer->pending_actions()->delete();
            }

            if ($callbackQuery && $customer->pending_actions()->exists()) {
                $customer->pending_actions()->delete();
            }

            if ($callbackQuery) {
                $this->answerCallbackQuery($callbackQuery->getId());
            }

            CommandService::process($update, $customer);
        } catch (\Exception $e) {
            Log::error('Error processing message', [
                'update_id' => $this->jsonData['update_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function createWelcomeSubscription(Customer $customer): void
    {
        try {
            $subscription = (new CustomerOnboardingService)->createWelcomeSubscription($customer);

            Log::info('Promo plan ensured for welcome subscription', [
                'plan_id' => $subscription->plan_id,
                'period' => $subscription->date_start?->diffInDays($subscription->date_end),
            ]);

            Log::info('Created welcome subscription for new customer', ['customer_id' => $customer->id]);
        } catch (\Exception $e) {
            Log::error('Error creating welcome subscription', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveCustomer(
        string $telegramId,
        ?string $telegramUsername,
        ?string $firstName,
        ?string $lastName,
    ): array {
        $customer = Customer::withTrashed()
            ->where('telegram_id', $telegramId)
            ->first();

        if ($customer) {
            $requiresOnboarding = $customer->trashed();

            $customer->forceFill([
                'telegram_username' => $telegramUsername,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            if ($requiresOnboarding) {
                $customer->restore();
                Log::info('Restored customer by telegram_id', ['customer_id' => $customer->id]);
            }

            $customer->save();

            return [$customer, $requiresOnboarding];
        }

        try {
            $customer = Customer::create([
                'telegram_id' => $telegramId,
                'telegram_username' => $telegramUsername,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            Log::info('Created new customer', ['customer_id' => $customer->id]);

            return [$customer, true];
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23505') {
                throw $exception;
            }

            $customer = Customer::withTrashed()
                ->where('telegram_id', $telegramId)
                ->firstOrFail();

            $requiresOnboarding = $customer->trashed();

            $customer->forceFill([
                'telegram_username' => $telegramUsername,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            if ($requiresOnboarding) {
                $customer->restore();
                Log::info('Restored customer after unique conflict', ['customer_id' => $customer->id]);
            }

            $customer->save();

            return [$customer, $requiresOnboarding];
        }
    }

    private function answerCallbackQuery(string $callbackQueryId): void
    {
        try {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $callbackQueryId,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to answer Telegram callback query', [
                'callback_query_id' => $callbackQueryId,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function createWelcomeVpnKey(Customer $customer): void
    {
        try {
            $vpnKey = (new CustomerOnboardingService)->createWelcomeVpnKey($customer);

            if ($vpnKey) {
                Log::info('Created welcome VPN key for new customer', ['customer_id' => $customer->id]);

                $this->sendWelcomeMessage($customer, $vpnKey->access_key);
            } else {
                Log::error('No server found');

                $this->sendWelcomeVpnUnavailableMessage($customer);
            }

        } catch (\Exception $e) {
            Log::error('Error creating welcome VPN key', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            $this->sendWelcomeVpnUnavailableMessage($customer);
        }
    }

    private function sendWelcomeVpnUnavailableMessage(Customer $customer): void
    {
        try {
            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => "⚠️ Ваша бесплатная подписка уже активирована, но сейчас сервер временно недоступен.\n\nПопробуйте получить VPN-ключ чуть позже командой /key.\n\nЕсли проблема не исчезнет, обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending welcome VPN unavailable message', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendWelcomeMessage(Customer $customer, string $access_url): void
    {
        try {
            $message = "🎉 <b>Добро пожаловать!</b>\n\n".
                "✅ Вам автоматически предоставлен бесплатный VPN на <b>10 дней</b>\n".
                "🔑 Ваш ключ VPN:\n\n".
                "<code>{$access_url}</code>\n\n".
                'Качайте приложение для подключения к VPN:';

            $keyboard = $this->buildAppsKeyboard($customer);

            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                ]),
            ]);

            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => '📱 Инструкции по подключению:',
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => 'Открыть', 'callback_data' => '/instructions']],
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ]);

            Log::info('Sent welcome message to new customer', ['customer_id' => $customer->id]);
        } catch (\Exception $e) {
            Log::error('Error sending welcome message', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildAppsKeyboard(Customer $customer): array
    {
        $keyboard = [];
        $onboardingService = new CustomerOnboardingService;
        $cabinetLinkService = new CustomerCabinetLinkService;

        foreach ($onboardingService->getWelcomeAppLinks() as $platform) {
            $keyboard[] = [[
                'text' => $platform['icon'].' '.$platform['label'],
                'url' => $platform['download_url'],
            ]];
        }

        $keyboard[] = [[
            'text' => '🌐 Открыть веб-кабинет',
            'web_app' => ['url' => $cabinetLinkService->getMiniAppUrl()],
        ]];

        $claimUrl = $onboardingService->getClaimUrl($customer);

        if ($claimUrl) {
            $keyboard[] = [[
                'text' => '✉️ Завершить регистрацию в браузере',
                'url' => $claimUrl,
            ]];
        }

        return $keyboard;
    }
}
