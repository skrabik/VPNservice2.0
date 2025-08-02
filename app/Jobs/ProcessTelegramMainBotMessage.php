<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\CustomerPendingAction;
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
}
