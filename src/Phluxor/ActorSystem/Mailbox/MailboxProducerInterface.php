<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Mailbox;

interface MailboxProducerInterface
{
    public function __invoke(): MailboxInterface;
}
