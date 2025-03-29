<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use Swoole\Atomic\Long;
use Swoole\Timer;

readonly class Throttle
{
    private Long $currentEvents;

    /**
     * @param int                $periodSeconds     seconds
     * @param Closure(int): void $throttledCallback
     */
    public function __construct(
        private int $maxEventsInPeriod,
        private int $periodSeconds,
        private Closure $throttledCallback,
    ) {
        $this->currentEvents = new Long(0);
    }

    public function shouldThrottle(): Valve
    {
        $tries = $this->currentEvents->add();
        if ($tries === 1) {
            $this->startTimer($this->periodSeconds);
        }

        if ($tries === $this->maxEventsInPeriod) {
            return Valve::Closing;
        }

        if ($tries > $this->maxEventsInPeriod) {
            return Valve::Closed;
        }

        return Valve::Open;
    }

    private function startTimer(int $duration): void
    {
        Timer::after($duration * 1000, function (): void {
            $n           = 0;
            $cur         = $this->currentEvents->get();
            $timesCalled = $n;
            if ($this->currentEvents->cmpset($cur, $n)) {
                $timesCalled = $cur;
            }

            if ($timesCalled <= $this->maxEventsInPeriod) {
                return;
            }

            ($this->throttledCallback)($timesCalled - $this->maxEventsInPeriod);
        });
    }
}
