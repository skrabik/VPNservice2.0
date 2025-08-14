<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\CustomerPendingAction;
use App\Models\Plan;
use App\Models\Server;
use App\Models\Subscription;
use App\Models\VpnKey;
use App\Services\OutlineService;
use App\Telegram\Services\CommandService;
use App\Telegram\Services\PreCheckoutQueryService;
use App\Telegram\Services\SuccessfulPaymentService;
use App\Telegram\Services\SupportTicketService;
use App\Telegram\TelegramManager;
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

    protected array $jsonData;

    public function __construct(array $json)
    {
        $this->jsonData = $json;
    }

    public function handle(): void
    {
        try {
            $update = new Update($this->jsonData);
            $message = $update->getMessage();

            if (! $message) {
                Log::warning('No message found in update');

                return;
            }

            $telegram_id = TelegramManager::extractTelegramId($update);

            if (! $telegram_id) {
                Log::warning('No telegram_id found in update');

                return;
            }

            $customer = Customer::where('telegram_id', $telegram_id)->first();

            if (! $customer) {
                $from = $message->getFrom();
                $customer = Customer::create([
                    'telegram_id' => $telegram_id,
                    'username' => $from->getUsername(),
                    'first_name' => $from->getFirstName(),
                    'last_name' => $from->getLastName(),
                ]);
                Log::info('Created new customer', ['customer_id' => $customer->id]);

                $this->createWelcomeSubscription($customer);

                $this->createWelcomeVpnKey($customer);

                return;
            }

            if ($update->getPreCheckoutQuery()) {
                PreCheckoutQueryService::process($update, $customer);

                return;
            }

            if ($update->getMessage()->getSuccessfulPayment()) {
                SuccessfulPaymentService::process($update, $customer);

                return;
            }

            if ($customer->pending_actions()->exists()) {
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

            CommandService::process($update, $customer);
        } catch (\Exception $e) {
            Log::error('Error processing message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function createWelcomeSubscription(Customer $customer): void
    {
        try {
            $promo_plan = Plan::where('slug', 'promo')->first();

            if (! $promo_plan) {
                $promo_plan = Plan::create([
                    'name' => 'Промо план',
                    'slug' => 'promo',
                    'description' => 'Бесплатный план на 7 дней для новых пользователей',
                    'price' => 0,
                    'period' => 7,
                    'is_active' => true,
                ]);

                Log::info('Created promo plan', ['plan_id' => $promo_plan->id]);
            }

            Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $promo_plan->id,
                'date_start' => now(),
                'date_end' => now()->addDays(7),
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
            $server = Server::first();

            if ($server) {
                $outline_service = new OutlineService($server);
                $password = $customer->telegram_id.'_'.time();
                $user = $outline_service->createUser($password);

                if ($user) {
                    VpnKey::create([
                        'customer_id' => $customer->id,
                        'server_id' => $server->id,
                        'server_user_id' => $user['id'],
                        'access_key' => $user['accessUrl'],
                        'server_type' => $server->type,
                        'is_active' => true,
                    ]);

                    Log::info('Created welcome VPN key for new customer', ['customer_id' => $customer->id]);

                    $this->sendWelcomeMessage($customer, $user['accessUrl'], $server->name);
                }
            } else {
                Log::error('No server found');

                Telegram::sendMessage([
                    'chat_id' => $customer->telegram_id,
                    'text' => 'Сервер не найден',
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error creating welcome VPN key', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendWelcomeMessage(Customer $customer, string $access_url, string $server_name): void
    {
        try {
            $message = "🎉 <b>Добро пожаловать!</b>\n\n".
                "✅ Вам автоматически предоставлен бесплатный VPN на <b>7 дней</b>\n".
                "🔑 Ваш ключ VPN для сервера {$server_name}:\n\n".
                "<code>{$access_url}</code>\n\n".
                'Качайте приложение для подключения к VPN:';

            $keyboard = [
                [['text' => '🤖 Android', 'url' => 'https://play.google.com/store/apps/details?id=org.outline.android.client']],
                [['text' => '🪟 Windows', 'url' => 'https://getoutline.org/get-started/step-1']],
                [['text' => '🍎 iOS', 'url' => 'https://apps.apple.com/us/app/outline-app/id1356177741']],
                [['text' => '🖥️ macOS', 'url' => 'https://getoutline.org/get-started/step-1']],
            ];

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
}
