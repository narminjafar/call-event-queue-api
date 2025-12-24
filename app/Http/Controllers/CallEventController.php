<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallEventRequest;
use App\Services\CallEventService;
use Illuminate\Http\JsonResponse;


class CallEventController extends Controller
{
    private CallEventService $callEventService;

    public function __construct(CallEventService $callEventService)
    {
        $this->callEventService = $callEventService;
    }

    public function store(StoreCallEventRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $result = $this->callEventService->processEvent($validatedData);

        $statusCode = $result['success'] ? 200 : 500;

        return response()->json([
            'status' => $result['success'] ? 'queued' : 'error',
            'message' => $result['message'],
            'data' => [
                'log_id' => $result['log_id'] ?? null,
                'call_id' => $result['call_id'] ?? null
            ]
        ], $statusCode);
    }
}
