<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Closure;
use Google\Protobuf\Internal\Message;

interface EventStoreInterface
{
    /** @param Closure(mixed): void $callback */
    public function getEvents(
        string $actorName,
        int $eventIndexStart,
        int $eventIndexEnd,
        Closure $callback,
    ): void;

    public function persistenceEvent(
        string $actorName,
        int $eventIndex,
        Message $event,
    ): void;
}
