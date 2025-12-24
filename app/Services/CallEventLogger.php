<?php

namespace App\Services;

use App\Contracts\EventLoggerInterface;
use App\Models\CallEventLog;
use Illuminate\Support\Facades\Log;
use Exception;

class CallEventLogger
{

    public function log(array $eventData): CallEventLog
    {
        try {
            $callEventLog = CallEventLog::create([
                'call_id' => $eventData['call_id'],
                'event_type' => $eventData['event_type'],
                'payload' => $eventData,
                'created_time' => now(),
            ]);

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


}

