<?php

namespace App\Http\Controllers;

use App\Services\Payments\ProcessYooKassaWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YooKassaWebhookController extends Controller
{
    public function __construct(
        private readonly ProcessYooKassaWebhookService $processYooKassaWebhookService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        }

        Log::info('YooKassa webhook received', [
            'event' => $payload['event'] ?? null,
            'object_id' => data_get($payload, 'object.id'),
        ]);

        $this->processYooKassaWebhookService->process($payload);

        return response()->json(['ok' => true]);
    }
}
