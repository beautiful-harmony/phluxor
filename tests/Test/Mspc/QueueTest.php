<?php

declare(strict_types=1);

namespace Test\Mspc;

use Phluxor\Mspc\Queue;
use PHPUnit\Framework\TestCase;
use Swoole\Event;

class QueueTest extends TestCase
{
    public function testQueuePushPop(): void
    {
        go(function (): void {
            $queue = new Queue();
            $queue->push(1);
            $queue->push(2);
            $queue->push(3);

            $this->assertEquals(1, $queue->pop()->value());
            $this->assertEquals(2, $queue->pop()->value());
            $this->assertEquals(3, $queue->pop()->value());
        });
        Event::wait();
    }

    public function testQueueIsEmpty(): void
    {
        go(function (): void {
            $queue = new Queue();
            $this->assertTrue($queue->isEmpty());
            $queue->push(1);
            $this->assertFalse($queue->isEmpty());
            $queue->pop();
            $this->assertTrue($queue->isEmpty());
        });
        Event::wait();
    }

    public function testQueuePushPopOneProducer(): void
    {
        go(function (): void {
            $queue    = new Queue();
            $producer = static function () use ($queue): void {
                $queue->push(1);
                $queue->push(2);
                $queue->push(3);
            };
            $producer();
            $this->assertEquals(1, $queue->pop()->value());
            $this->assertEquals(2, $queue->pop()->value());
            $this->assertEquals(3, $queue->pop()->value());
        });
        Event::wait();
    }
}
