<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Google\Protobuf\Internal\Message;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;

interface PersistentInterface
{
    public function init(ProviderInterface $provider, ContextInterface|ReceiverInterface $context): void;

    public function persistenceReceive(Message $message): void;

    public function persistenceSnapshot(Message $snapshot): void;

    public function recovering(): bool;

    public function name(): string;

    public function receiveRecover(
        mixed $message,
    ): void;
}
