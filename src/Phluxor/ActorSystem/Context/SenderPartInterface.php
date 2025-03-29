<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\Ref;

interface SenderPartInterface
{
    /**
     * returns the Ref of actor that sent currently processed message
     */
    public function sender(): Ref|null;

    /**
     * sends a message to the actor identified by the Ref
     */
    public function send(Ref|null $pid, mixed $message): void;

    /**
     * sends a message to the actor identified by the Ref and expects a response
     */
    public function request(Ref|null $pid, mixed $message): void;

    public function requestWithCustomSender(Ref|null $pid, mixed $message, Ref|null $sender): void;

    /**
     * sends a message to the actor identified by the Ref and expects a response within a specified time
     */
    public function requestFuture(Ref|null $pid, mixed $message, int $duration): Future;
}
