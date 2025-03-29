<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Child;

use DateInterval;
use DateTimeImmutable;

use function count;

class RestartStatistics
{
    /** @param DateTimeImmutable[] $failureTimes */
    public function __construct(
        private array $failureTimes = [],
    ) {
    }

    /**
     * returns failure count
     */
    public function failureCount(): int
    {
        return count($this->failureTimes);
    }

    /**
     * increases the associated actors' failure count
     */
    public function fail(): void
    {
        $this->failureTimes[] = new DateTimeImmutable();
    }

    /**
     * the associated actors' failure count
     */
    public function reset(): void
    {
        $this->failureTimes = [];
    }

    /**
     * returns number of failures within a given duration
     */
    public function numberOfFailures(DateInterval $withinDuration): int
    {
        if ($withinDuration->s === 0) {
            return count($this->failureTimes);
        }

        $num      = 0;
        $currTime = new DateTimeImmutable();
        foreach ($this->failureTimes as $time) {
            if ($currTime->getTimestamp() - $time->getTimestamp() >= $withinDuration->s) {
                continue;
            }

            $num++;
        }

        return $num;
    }

    public function append(DateTimeImmutable $time): void
    {
        $this->failureTimes[] = $time;
    }
}
