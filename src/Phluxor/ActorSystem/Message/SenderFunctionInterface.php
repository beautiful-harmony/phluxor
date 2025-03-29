<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Ref;

interface SenderFunctionInterface
{
    public function __invoke(
        SenderInterface $context,
        Ref|null $target,
        MessageEnvelope $messageEnvelope,
    ): void;
}
