<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;

readonly class EventStreamProcess implements ProcessInterface
{
    public function __construct(
        private ActorSystem $actorSystem,
    ) {
    }

    public function sendUserMessage(Ref|null $pid, mixed $message): void
    {
        $msg = ActorSystem\Message\MessageEnvelope::unwrapEnvelope($message);
        $this->actorSystem->getEventStream()?->publish($msg['message']);
    }

    public function sendSystemMessage(Ref $pid, mixed $message): void
    {
        // none
    }

    public function stop(Ref $pid): void
    {
        // none
    }
}
