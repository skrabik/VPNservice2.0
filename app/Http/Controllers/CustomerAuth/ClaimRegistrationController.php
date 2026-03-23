<?php

namespace App\Http\Controllers\CustomerAuth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ClaimRegistrationController extends Controller
{
    public function create(Customer $customer): RedirectResponse|View
    {
        if (filled($customer->email) && filled($customer->password)) {
            return redirect()
                ->route('customer.login')
                ->with('status', 'Веб-доступ для этого аккаунта уже настроен. Войдите по email и паролю.');
        }

        return view('customer.auth.claim', [
            'customer' => $customer,
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:256'],
            'last_name' => ['nullable', 'string', 'max:256'],
            'email' => ['required', 'string', 'email', 'max:256', Rule::unique('customers', 'email')->ignore($customer->id)],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $customer->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ])->save();

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()
            ->route('customer.dashboard')
            ->with('status', 'Веб-доступ успешно настроен.');
    }
}
