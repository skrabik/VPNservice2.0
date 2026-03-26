<?php

namespace App\Http\Controllers\CustomerAuth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerCabinetLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly CustomerCabinetLinkService $cabinetLinkService,
    ) {}

    public function create(): View
    {
        return view('customer.auth.login', [
            'botUrl' => $this->cabinetLinkService->getBotUrl(),
            'miniAppUrl' => $this->cabinetLinkService->getMiniAppUrl(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::query()
            ->where('email', $credentials['email'])
            ->first();

        if (! $customer || blank($customer->password) || ! Hash::check($credentials['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль.',
            ]);
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $request->session()->forget('customer.is_telegram_mini_app');

        return redirect()->intended(route('customer.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
