<?php

declare(strict_types=1);

namespace Test\ActorSystem\Mailbox;

interface UserMessageReceiveHandlerInterface
{
    public function __invoke(mixed $message): void;
}
