<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerAuthLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TelegramConnectionController extends Controller
{
    public function __construct(
        private readonly CustomerAuthLinkService $authLinkService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        return redirect()->away($this->authLinkService->createTelegramLinkUrl($customer));
    }
}
