<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Services\CustomerVpnKeyService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KeyController extends Controller
{
    public function __construct(
        private readonly CustomerVpnKeyService $vpnKeyService,
    ) {}

    public function index(Request $request): View
    {
        $customer = $request->user('customer');
        $currentKey = $this->vpnKeyService->getCurrentKey($customer);
        $availableServers = $this->vpnKeyService->getAvailableServers();

        return view('customer.keys', [
            'currentKey' => $currentKey,
            'availableServers' => $availableServers,
            'hasActiveSubscription' => $this->vpnKeyService->getActiveSubscription($customer) !== null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $request->user('customer');

        $validated = $request->validate([
            'server_id' => ['required', 'integer', 'exists:servers,id'],
        ]);

        $server = Server::query()->findOrFail($validated['server_id']);

        try {
            $vpnKey = $this->vpnKeyService->createKeyForServer($customer, $server);
        } catch (DomainException $exception) {
            return back()->withErrors([
                'server_id' => $exception->getMessage(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Customer cabinet key creation failed', [
                'customer_id' => $customer->id,
                'server_id' => $server->id,
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ]);

            return back()->withErrors([
                'server_id' => 'Не удалось создать ключ. Попробуйте позже.',
            ]);
        }

        return redirect()
            ->route('customer.keys')
            ->with('status', 'Новый VPN-ключ для сервера '.$server->hostname.' успешно создан.')
            ->with('generated_key_id', $vpnKey->id);
    }
}
