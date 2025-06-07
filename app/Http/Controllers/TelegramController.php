<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTelegramMainBotMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function setTelegramWebhook()
    {
        $bots = [
            env('TELEGRAM_MAIN_BOT_NAME') => env('TELEGRAM_MAIN_BOT_TOKEN'),
        ];

        $result = [];
        foreach ($bots as $bot_name => $bot_token) {
            $result[$bot_name] = $this->setWebhook($bot_token);
        }
        dump($result);
    }

    private function setWebhook($token)
    {
        $url = route('process_webhook', ['token' => $token]);

        // подменяем урл на ngrok для локального окружения
        // $url = str_replace('http://localhost:8000', 'https://2xexxlu64mxd.share.zrok.io', $url);

        return Telegram::setWebhook(['url' => $url]);
    }

    public function processWebhook(Request $request, $token)
    {
        if ($token === env('TELEGRAM_MAIN_BOT_TOKEN')) {
            $data = json_decode($request->getContent(), true);

            Log::info('Telegram Main Bot Webhook Data:', $data);

            ProcessTelegramMainBotMessage::dispatch($data);
        }
    }
}
