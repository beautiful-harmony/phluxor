<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

interface MessageInvokerInterface
{
    public function invokeSystemMessage(mixed $message): void;

    public function invokeUserMessage(mixed $message): void;

    public function escalateFailure(mixed $reason, mixed $message): void;
}
