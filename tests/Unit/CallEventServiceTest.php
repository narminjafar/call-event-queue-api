<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CallEventService;
use App\Http\Repositories\CallEventlogRepository;
use App\Interfaces\MessageQueueInterface;
use App\Models\CallEventLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class CallEventServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_process_event_successfully(): void
    {
        $mockRepo = Mockery::mock(CallEventlogRepository::class);

        $mockLog = new CallEventLog();
        $mockLog->id = 1;
        $mockLog->call_id = 'CALL-123';
        $mockLog->event_type = 'call_started';

        $mockRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn($mockLog);

        $mockQueue = Mockery::mock(MessageQueueInterface::class);
        $mockQueue->shouldReceive('publish')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn(true);

        $service = new CallEventService($mockRepo, $mockQueue);

        $eventData = [
            'call_id' => 'CALL-123',
            'caller_number' => '+994501234567',
            'called_number' => '+994551234567',
            'event_type' => 'call_started',
            'timestamp' => now()->toIso8601String(),
        ];

        $result = $service->processEvent($eventData);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['log_id']);
        $this->assertEquals('CALL-123', $result['call_id']);
        $this->assertTrue($result['queued']);
    }

    public function test_process_event_queue_fails(): void
    {
        $mockRepo = Mockery::mock(CallEventlogRepository::class);

        $mockLog = new CallEventLog();
        $mockLog->id = 2;
        $mockLog->call_id = 'CALL-789';
        $mockLog->event_type = 'call_started';

        $mockRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockLog);

        $mockQueue = Mockery::mock(MessageQueueInterface::class);
        $mockQueue->shouldReceive('publish')
            ->once()
            ->andReturn(false);

        $service = new CallEventService($mockRepo, $mockQueue);

        $eventData = [
            'call_id' => 'CALL-789',
            'caller_number' => '+994501234567',
            'called_number' => '+994551234567',
            'event_type' => 'call_started',
            'timestamp' => now()->toIso8601String(),
        ];

        $result = $service->processEvent($eventData);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['log_id']);
        $this->assertFalse($result['queued']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
