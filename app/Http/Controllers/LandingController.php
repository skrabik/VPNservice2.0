<?php

namespace App\Http\Controllers;

use App\Models\TelegramCommandLog;
use App\Services\CustomerCabinetLinkService;

class LandingController extends Controller
{
    public function __construct(
        private CustomerCabinetLinkService $customerCabinetLinkService
    ) {}

    public function index()
    {
        return view('landing.index', [
            'telegramBotUrl' => $this->customerCabinetLinkService->getBotUrl(),
        ]);
    }

    public function clickStat()
    {
        TelegramCommandLog::create([
            'customer_id' => 1,
            'command_name' => 'Перешёл по ссылке на бота в телеге',
            'action' => 'Перешёл по ссылке на бота в телеге',
        ]);

        return redirect()->away($this->customerCabinetLinkService->getBotUrl());
    }
}
