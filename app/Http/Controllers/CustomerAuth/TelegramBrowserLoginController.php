<?php

namespace App\Http\Controllers\CustomerAuth;

use App\Http\Controllers\Controller;
use App\Services\CustomerAuthLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramBrowserLoginController extends Controller
{
    public function __construct(
        private readonly CustomerAuthLinkService $authLinkService,
    ) {}

    public function store(Request $request, string $token): RedirectResponse
    {
        $customer = $this->authLinkService->consumeBrowserLoginToken($token);

        if (! $customer) {
            return redirect()
                ->route('customer.login')
                ->withErrors([
                    'telegram' => 'Ссылка для входа через Telegram устарела или уже была использована.',
                ]);
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $request->session()->forget('customer.is_telegram_mini_app');

        return redirect()
            ->route('customer.dashboard')
            ->with('status', 'Вход через Telegram-ссылку выполнен успешно.');
    }
}
