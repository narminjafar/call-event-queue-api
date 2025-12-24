<?php

namespace App\Interfaces;

interface MessageQueueInterface
{
    public function publish(array $data): bool;

    public function connect(): bool;

    public function disconnect(): void;

    public function ensureQueueExists(string $queueName): bool;
}
