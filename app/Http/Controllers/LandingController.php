<?php

namespace App\Http\Controllers;

use App\Models\TelegramCommandLog;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing.index');
    }

    public function clickStat()
    {
        TelegramCommandLog::create([
            'customer_id' => 1,
            'command_name' => 'Перешёл по ссылке на бота в телеге',
            'action' => 'Перешёл по ссылке на бота в телеге',
        ]);

        return redirect('https://t.me/quantum_shield_bot');
    }
}
