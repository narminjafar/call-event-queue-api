<?php

namespace App\Services;

use App\Interfaces\MessageQueueInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;
use Exception;

class RabbitMQService implements MessageQueueInterface
{
    private ?AMQPStreamConnection $connection = null;
    private $channel = null;
    private string $queueName;
    private array $config;

    public function __construct(?string $queueName = null)
    {
        $this->queueName = $queueName ?? config('queue.connections.rabbitmq.queue', 'call-events');
        $this->config = [
            'host' => config('queue.connections.rabbitmq.host', '127.0.0.1'),
            'port' => (int) config('queue.connections.rabbitmq.port', 5672),
            'user' => config('queue.connections.rabbitmq.user', 'guest'),
            'password' => config('queue.connections.rabbitmq.password', 'guest'),
            'vhost' => config('queue.connections.rabbitmq.vhost', '/'),
        ];
    }

    public function connect(): bool
    {
        if ($this->connection !== null) return true;

        try {
            $this->validateConfig();

            $this->connection = new AMQPStreamConnection(...array_values($this->config));
            $this->channel = $this->connection->channel();

            Log::info('RabbitMQ qoşuldu', ['host' => $this->config['host']]);
            return true;
        } catch (Exception $e) {
            Log::error('RabbitMQ qoşulma xətası: ' . $e->getMessage());
            return false;
        }
    }

    public function ensureQueueExists(string $queueName): bool
    {
        try {
            if (!$this->channel && !$this->connect()) return false;

            $this->channel->queue_declare($queueName, false, true, false, false);
            return true;
        } catch (Exception $e) {
            Log::error('Queue yaratma xətası: ' . $e->getMessage());
            return false;
        }
    }

    public function publish(array $data): bool
    {
        try {
            if (!$this->channel && !$this->connect()) {
                throw new Exception('RabbitMQ qoşulması alınmadı');
            }

            if (!$this->ensureQueueExists($this->queueName)) {
                throw new Exception('Queue yaradıla bilmədi');
            }

            $msg = new AMQPMessage(json_encode($data, JSON_UNESCAPED_UNICODE), [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'application/json',
                'content_encoding' => 'utf-8',
                'timestamp' => time(),

            ]);

            $this->channel->basic_publish($msg, '', $this->queueName);

            Log::info('Mesaj göndərildi', ['call_id' => $data['call_id'] ?? null]);
            return true;
        } catch (Exception $e) {
            Log::error('Mesaj göndərmə xətası: ' . $e->getMessage());
            return false;
        }
    }

    public function disconnect(): void
    {
        try {
            if ($this->channel) $this->channel->close();
            if ($this->connection) $this->connection->close();
            $this->channel = $this->connection = null;
        } catch (Exception $e) {
            Log::error('Bağlanma xətası: ' . $e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    private function validateConfig(): void
    {
        if (empty($this->config['host'])) {
            throw new Exception('RABBITMQ_HOST konfiqurasiyası yoxdur');
        }
        if (empty($this->config['port'])) {
            throw new Exception('RABBITMQ_PORT konfiqurasiyası yoxdur');
        }
    }

    public function setQueue(string $queueName): self
    {
        $this->queueName = $queueName;
        return $this;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function getChannel()
{
    if (!$this->channel && !$this->connect()) {
        throw new Exception('RabbitMQ channel is not available.');
    }
    return $this->channel;
}

}
