<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

interface ProcessInterface
{
    /**
     * Sends a user message.
     *
     * @param Ref|null $pid     The reference to an actor.
     * @param mixed    $message The message to send.
     */
    public function sendUserMessage(Ref|null $pid, mixed $message): void;

    public function sendSystemMessage(Ref $pid, mixed $message): void;

    public function stop(Ref $pid): void;
}
