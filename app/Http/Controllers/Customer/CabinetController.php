<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CustomerCabinetLinkService;
use App\Services\CustomerInstructionService;
use App\Services\CustomerStatusService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function __construct(
        private readonly CustomerStatusService $statusService,
        private readonly CustomerInstructionService $instructionService,
        private readonly CustomerCabinetLinkService $cabinetLinkService,
    ) {}

    public function dashboard(Request $request): View
    {
        $customer = $request->user('customer');
        $overview = $this->statusService->getOverview($customer);

        return view('customer.dashboard', [
            'customer' => $customer,
            'overview' => $overview,
            'botUrl' => $this->cabinetLinkService->getBotUrl(),
        ]);
    }

    public function status(Request $request): View
    {
        return view('customer.status', [
            'overview' => $this->statusService->getOverview($request->user('customer')),
        ]);
    }

    public function instructions(Request $request): View
    {
        $selectedType = $this->instructionService->normalizeType($request->query('type'));

        return view('customer.instructions', [
            'selectedType' => $selectedType,
            'platforms' => $this->instructionService->getPlatforms(),
            'instructionHtml' => nl2br($this->instructionService->getInstructions($selectedType)),
        ]);
    }

    public function pay(): View
    {
        return view('customer.pay', [
            'botUrl' => $this->cabinetLinkService->getBotUrl(),
        ]);
    }
}
