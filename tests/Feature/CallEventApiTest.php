<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Interfaces\MessageQueueInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class CallEventApiTest extends TestCase
{
    use RefreshDatabase;

    private string $apiToken;
    private string $endpoint = '/api/call-events';

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiToken =  config('services.api.token');
    }

    public function test_can_create_call_event_successfully(): void
    {
        $mockQueue = Mockery::mock(MessageQueueInterface::class);
        $mockQueue->shouldReceive('publish')
            ->once()
            ->andReturn(true);

        $this->app->instance(MessageQueueInterface::class, $mockQueue);

        $payload = [
            'call_id' => 'CALL-123456',
            'caller_number' => '+994508611069',
            'called_number' => '+994998601069',
            'event_type' => 'call_started',
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Accept' => 'application/json',
        ])->postJson($this->endpoint, $payload);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 'queued',
            ]);

        $this->assertDatabaseHas('call_event_logs', [
            'call_id' => 'CALL-123456',
            'event_type' => 'call_started',
            'payload' => json_encode($payload),
            'created_time' => now()->toDateTimeString(),
        ]);
    }

    public function test_duration_required_for_call_ended_event(): void
    {
        $payload = [
            'call_id' => 'CALL-123456',
            'caller_number' => '+994501234567',
            'called_number' => '+994551234567',
            'event_type' => 'call_ended',
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Accept' => 'application/json',
        ])->postJson($this->endpoint, $payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['duration']);
    }

    public function test_endpoint_requires_authentication(): void
    {
        $payload = [
            'call_id' => 'CALL-123456',
            'caller_number' => '+994501234567',
            'called_number' => '+994551234567',
            'event_type' => 'call_started',
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(401);
    }

    public function test_endpoint_rejects_invalid_token(): void
    {
        $payload = [
            'call_id' => 'CALL-123456',
            'caller_number' => '+994501234567',
            'called_number' => '+994551234567',
            'event_type' => 'call_started',
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json',
        ])->postJson($this->endpoint, $payload);

        $response->assertStatus(401);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
