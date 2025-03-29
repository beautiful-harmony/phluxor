<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Channel;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use Swoole\Coroutine\Channel;

class TypedChannel
{
    private Channel $channel;
    private Ref $ref;

    /**
     * @param Closure(mixed): bool $specification
     * @param int                  $bufferSize
     * <code>
     *     $channel = new TypedChannel(
     *         $actorSystem,
     *         fn(mixed $message): bool => is_string($message)
     *    });
     * </code>
     */
    public function __construct(
        private ActorSystem $actorSystem,
        private readonly Closure $specification,
        private readonly int $bufferSize = 1,
    ) {
        $this->channel = new Channel($this->bufferSize);
        $this->ref     = $this->actorSystem->root()->spawn($this->createProps());
    }

    /**
     * Send a message to the channel
     */
    public function result(): mixed
    {
        return $this->channel->pop();
    }

    /**
     * actor reference
     */
    public function getRef(): Ref
    {
        return $this->ref;
    }

    /**
     * Close the channel
     * call this method when you want to close the channel
     */
    public function close(): void
    {
        $this->actorSystem->root()->stop($this->ref);
        $this->channel->close();
    }

    /**
     * Check if the message is defined or not
     */
    private function isDefinedMessage(mixed $msg): bool
    {
        foreach (
            [
                new ActorSystem\Message\DetectAutoReceiveMessage($msg),
                new ActorSystem\Message\DetectSystemMessage($msg),
            ] as $expect
        ) {
            if ($expect->isMatch()) {
                return true;
            }
        }

        return false;
    }

    private function createProps(): ActorSystem\Props
    {
        return ActorSystem\Props::fromFunction(
            new ActorSystem\Message\ReceiveFunction(
                function (ActorSystem\Context\ContextInterface $context): void {
                    $msg           = $context->message();
                    $specification = $this->specification;
                    switch (true) {
                        // is defined message or not
                        case $this->isDefinedMessage($msg):
                            return;

                        case $specification($msg):
                            $this->channel->push($msg);

                            return;
                    }
                },
            ),
        );
    }
}
