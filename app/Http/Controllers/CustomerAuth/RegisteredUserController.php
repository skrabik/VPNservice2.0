<?php

namespace App\Http\Controllers\CustomerAuth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('customer.auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:256'],
            'last_name' => ['nullable', 'string', 'max:256'],
            'email' => ['required', 'string', 'email', 'max:256'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $customer = DB::transaction(function () use ($validated) {
            $existingCustomer = Customer::query()
                ->where('email', $validated['email'])
                ->first();

            if ($existingCustomer && filled($existingCustomer->password)) {
                throw ValidationException::withMessages([
                    'email' => 'Пользователь с таким email уже зарегистрирован.',
                ]);
            }

            if ($existingCustomer) {
                $existingCustomer->forceFill([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'] ?: $existingCustomer->last_name,
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ])->save();

                return $existingCustomer;
            }

            return Customer::query()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
        });

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }
}
