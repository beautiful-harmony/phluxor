<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use Phluxor\ActorSystem\Exception\FutureTimeoutException;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Metrics\MetricsSystemTrait;
use Phluxor\ActorSystem\ProtoBuf\DeadLetterResponse;
use Phluxor\Metrics\ActorMetrics;
use Phluxor\Metrics\PhluxorMetrics;

readonly class FutureProcess implements ProcessInterface
{
    use MetricsSystemTrait;

    public function __construct(
        private Future $future,
    ) {
    }

    /**
     * sendUserMessage sends a message asynchronously to the given PID
     */
    public function sendUserMessage(Ref|null $pid, mixed $message): void
    {
        $this->instrument();
        $msg = MessageEnvelope::unwrapEnvelope($message);
        $res = $msg['message'];
        if ($res instanceof DeadLetterResponse) {
            $this->future->setResult(null);
            $this->future->setError(
                new FutureTimeoutException('future: dead letter'),
            );
        } else {
            $this->future->setResult($res);
        }

        if ($pid === null) {
            return;
        }

        $this->stop($pid);
    }

    public function sendSystemMessage(Ref $pid, mixed $message): void
    {
        $this->instrument();
        $this->future->setResult($message);
        $this->stop($pid);
    }

    public function stop(Ref $pid): void
    {
        $this->future->stop($pid);
    }

    public function getFuture(): Future
    {
        return $this->future;
    }

    private function instrument(): void
    {
        $actorSystem   = $this->future->getActorSystem();
        $metricsSystem = $this->enabledMetricsSystem($actorSystem);
        if (! $metricsSystem) {
            return;
        }

        $instruments = $metricsSystem->metrics()->find(PhluxorMetrics::INTERNAL_ACTOR_METRICS);
        if (! ($instruments instanceof ActorMetrics)) {
            return;
        }

        if ($this->future->isError() === null) {
            $instruments->getFuturesCompletedCount()
                ->add(
                    1,
                    Attributes::create([
                        'address' => $actorSystem->address(),
                        'actor_ref' => (string) $this->future->pid(),
                    ]),
                );

            return;
        }

        $instruments->getFuturesTimedOutCount()
            ->add(
                1,
                Attributes::create([
                    'address' => $actorSystem->address(),
                    'actor_ref' => (string) $this->future->pid(),
                ]),
            );
    }
}
