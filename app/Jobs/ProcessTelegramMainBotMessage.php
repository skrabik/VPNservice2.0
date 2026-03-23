<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\CustomerPendingAction;
use App\Models\Plan;
use App\Models\Server;
use App\Models\Subscription;
use App\Telegram\Services\CommandService;
use App\Telegram\Services\PreCheckoutQueryService;
use App\Telegram\Services\SuccessfulPaymentService;
use App\Telegram\Services\SupportTicketService;
use App\Telegram\TelegramManager;
use App\Services\VpnProviders\VpnAccessManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class ProcessTelegramMainBotMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const PROMO_PERIOD_DAYS = 15;

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

            $customer = Customer::where('telegram_id', $telegram_id)->first();

            if (! $customer) {
                $from = $callbackQuery?->getFrom() ?? $preCheckoutQuery?->getFrom() ?? $message?->getFrom();
                if (! $from) {
                    Log::warning('No from payload found in update', [
                        'telegram_id' => $telegram_id,
                    ]);

                    return;
                }

                $customer = Customer::create([
                    'telegram_id' => $telegram_id,
                    'telegram_username' => $from->getUsername(),
                    'first_name' => $from->getFirstName(),
                    'last_name' => $from->getLastName(),
                ]);
                Log::info('Created new customer', ['customer_id' => $customer->id]);

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
            $promo_plan = Plan::updateOrCreate(
                ['slug' => 'promo'],
                [
                    'title' => 'Промо план',
                    'description' => 'Бесплатный план на 15 дней для новых пользователей',
                    'price' => 0,
                    'stars' => 1,
                    'period' => self::PROMO_PERIOD_DAYS,
                    'active' => true,
                ]
            );

            Log::info('Promo plan ensured for welcome subscription', [
                'plan_id' => $promo_plan->id,
                'period' => $promo_plan->period,
            ]);

            Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $promo_plan->id,
                'date_start' => now(),
                'date_end' => now()->addDays(self::PROMO_PERIOD_DAYS),
            ]);

            Log::info('Created welcome subscription for new customer', ['customer_id' => $customer->id]);
        } catch (\Exception $e) {
            Log::error('Error creating welcome subscription', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function createWelcomeVpnKey(Customer $customer): void
    {
        try {
            $server = Server::query()
                ->where('active', true)
                ->orderByDesc('id')
                ->first();

            if ($server) {
                $activeSubscription = $customer->subscriptions()
                    ->where('date_end', '>', now())
                    ->latest('date_end')
                    ->first();

                $vpnKey = (new VpnAccessManager)->createForCustomer($server, $customer, [
                    'expires_at' => $activeSubscription?->date_end,
                ]);

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
                "✅ Вам автоматически предоставлен бесплатный VPN на <b>15 дней</b>\n".
                "🔑 Ваш ключ VPN:\n\n".
                "<code>{$access_url}</code>\n\n".
                'Качайте приложение для подключения к VPN:';

            $keyboard = $this->buildAppsKeyboard();

            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                ]),
            ]);

            Log::info('Sent welcome message to new customer', ['customer_id' => $customer->id]);
        } catch (\Exception $e) {
            Log::error('Error sending welcome message', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
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

    private function buildAppsKeyboard(): array
    {
        return [
            [['text' => '🤖 Android', 'url' => 'https://play.google.com/store/apps/details?id=com.v2ray.ang']],
            [['text' => '🍎 iOS', 'url' => 'https://apps.apple.com/us/app/streisand/id6450534064']],
            [['text' => '🪟 Windows', 'url' => 'https://github.com/2dust/v2rayN/releases']],
            [['text' => '🖥️ macOS', 'url' => 'https://github.com/yichengchen/clashX/releases']],
        ];
    }
}
