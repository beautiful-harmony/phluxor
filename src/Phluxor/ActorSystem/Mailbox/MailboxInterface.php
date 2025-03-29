<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

use Phluxor\ActorSystem\Dispatcher\DispatcherInterface;

interface MailboxInterface
{
    public function postUserMessage(mixed $message): void;

    public function postSystemMessage(mixed $message): void;

    public function start(): void;

    public function userMessageCount(): int;

    public function registerHandlers(
        MessageInvokerInterface $invoker,
        DispatcherInterface $dispatcher,
    ): void;
}
