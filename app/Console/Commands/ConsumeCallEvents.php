<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class ConsumeCallEvents extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume messages from call-events queue using RabbitMQService';

    public function handle()
    {
        $this->info('Starting RabbitMQ consumer...');

        $rabbit = new RabbitMQService('call-events');

        if (!$rabbit->connect()) {
            die("RabbitMQ-a qoşulmaq alınmadı");
        }

        $channel = $rabbit->getChannel();
        $queue = $rabbit->getQueueName();

        $callback = function (AMQPMessage $msg) {
            $data = json_decode($msg->getBody(), true);
            Log::info('RabbitMQ message received:', $data);
            echo " Received: " . json_encode($data) . "\n";
            $msg->ack();
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }
    }
}
