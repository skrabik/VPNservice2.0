<?php

namespace App\Http\Controllers\CustomerAuth;

use App\Http\Controllers\Controller;
use App\Services\CustomerTelegramAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TelegramAuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly CustomerTelegramAuthService $telegramAuthService,
    ) {}

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'init_data' => ['required', 'string'],
        ]);

        $customer = $this->telegramAuthService->authenticateByInitData($validated['init_data']);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $request->session()->put('customer.is_telegram_mini_app', true);

        return redirect()
            ->intended(route('customer.dashboard'))
            ->with('status', 'Вход через Telegram выполнен успешно.');
    }
}
