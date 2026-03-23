<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CustomerSupportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function __construct(
        private readonly CustomerSupportService $supportService,
    ) {}

    public function index(Request $request): View
    {
        $customer = $request->user('customer');

        return view('customer.support', [
            'tickets' => $customer->supportTickets()
                ->latest('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $this->supportService->createTicket($request->user('customer'), $validated['message']);

        return redirect()
            ->route('customer.support')
            ->with('status', 'Ваше обращение отправлено. Мы скоро ответим.');
    }
}
