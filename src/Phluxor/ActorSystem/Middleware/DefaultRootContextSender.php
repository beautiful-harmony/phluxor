<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Ref;

readonly class DefaultRootContextSender implements SenderFunctionInterface
{
    public function __construct(
        private ActorSystem $actorSystem,
    ) {
    }

    public function __invoke(
        SenderInterface $context,
        Ref|null $target,
        MessageEnvelope $messageEnvelope,
    ): void {
        if (! ($target instanceof Ref)) {
            return;
        }

        $target->sendUserMessage($this->actorSystem, $messageEnvelope);
    }
}
