<?php

namespace App\Services;

use App\Interfaces\MessageQueueInterface;
use App\Http\Repositories\CallEventlogRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class CallEventService
{
    private CallEventlogRepository $callEventlogRepo;
    private MessageQueueInterface $queue;


    public function __construct(
        CallEventlogRepository $callEventlogRepo,
        MessageQueueInterface $queue
    ) {
        $this->callEventlogRepo = $callEventlogRepo;
        $this->queue = $queue;
    }

    public function create(array $eventData)
    {
        $eventData['payload'] = $eventData;
        $eventData['created_time'] = now();

        try {
            $callEventLog = $this->callEventlogRepo->create($eventData);

            Log::info('Call event loqlandı', [
                'log_id' => $callEventLog->id,
                'call_id' => $eventData['call_id'],
                'event_type' => $eventData['event_type']
            ]);

            return $callEventLog;

        } catch (Exception $e) {
            Log::error('Call event loqlama xətası', [
                'error' => $e->getMessage(),
                'data' => $eventData
            ]);
            throw $e;
        }
    }


    public function processEvent(array $eventData): array
    {
        DB::beginTransaction();

        try {
            $callEventLog = $this->create($eventData);

            $queuePayload = $this->prepareQueuePayload($eventData, $callEventLog->id);

            $published = $this->queue->publish($queuePayload);

            if (!$published) {
                Log::warning('RabbitMQ-a göndərmə uğursuz, lakin event loqlandı', [
                    'call_id' => $eventData['call_id'],
                    'log_id' => $callEventLog->id
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'log_id' => $callEventLog->id,
                'call_id' => $eventData['call_id'],
                'queued' => $published,
                'message' => 'Call event uğurla qəbul edildi və queue-a əlavə olundu'
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Call event emalı xətası', [
                'error' => $e->getMessage(),
                'data' => $eventData,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Call event emalı zamanı xəta baş verdi',
                'error' => $e->getMessage()
            ];
        }
    }

    private function prepareQueuePayload(array $eventData, int $logId): array
    {
        return [
            'log_id' => $logId,
            'call_id' => $eventData['call_id'],
            'caller_number' => $eventData['caller_number'],
            'called_number' => $eventData['called_number'],
            'event_type' => $eventData['event_type'],
            'timestamp' => $eventData['timestamp'],
            'duration' => $eventData['duration'] ?? null,
            'queued_at' => now()->toIso8601String(),
        ];
    }




}
