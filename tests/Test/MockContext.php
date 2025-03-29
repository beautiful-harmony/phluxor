<?php

declare(strict_types=1);

namespace Test;

use Closure;
use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\ProtoBuf\PID;
use Phluxor\ActorSystem\ReadonlyMessageHeaderInterface;
use Phluxor\ActorSystem\ReenterAfterInterface;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\SpawnResult;
use Phluxor\Value\ContextExtensionId;
use Phluxor\Value\ExtensionInterface;
use Psr\Log\LoggerInterface;

class MockContext implements ContextInterface
{
    /** @var Closure(): mixed|null */
    private Closure|null $messageHandler = null;

    /** @var Closure(mixed): void|null */
    private Closure|null $respondHandler = null;

    /** @var Closure(?Ref, mixed): void|null */
    private Closure|null $sendHandler = null;

    /** @var Closure(?Ref, mixed, ?Ref): void|null */
    private Closure|null $requestWithCustomSenderHandler = null;

    /** @param Closure(): mixed $closure */
    public function messageHandle(Closure $closure): void
    {
        $this->messageHandler = $closure;
    }

    public function respondHandle(Closure $closure): void
    {
        $this->respondHandler = $closure;
    }

    public function sendHandle(Closure $closure): void
    {
        $this->sendHandler = $closure;
    }

    public function requestWithCustomSenderHandle(Closure $closure): void
    {
        $this->requestWithCustomSenderHandler = $closure;
    }

    public function receiveTimeout(): DateInterval
    {
        // TODO: Implement receiveTimeout() method.
    }

    public function children(): array
    {
        // TODO: Implement children() method.
    }

    public function respond(mixed $response): void
    {
        if ($this->respondHandler === null) {
            return;
        }

        $handle = $this->respondHandler;
        $handle($response);
    }

    public function stash(): void
    {
        // TODO: Implement stash() method.
    }

    public function watch(Ref $pid): void
    {
        // TODO: Implement watch() method.
    }

    public function unwatch(Ref $pid): void
    {
        // TODO: Implement unwatch() method.
    }

    public function setReceiveTimeout(DateInterval $dateInterval): void
    {
        // TODO: Implement setReceiveTimeout() method.
    }

    public function cancelReceiveTimeout(): void
    {
        // TODO: Implement cancelReceiveTimeout() method.
    }

    public function forward(Ref $pid): void
    {
        // TODO: Implement forward() method.
    }

    public function reenterAfter(Future $future, ReenterAfterInterface $reenterAfter): void
    {
        // TODO: Implement reenterAfter() method.
    }

    public function get(ContextExtensionId $id): ExtensionInterface
    {
        // TODO: Implement get() method.
    }

    public function set(ExtensionInterface $extension): void
    {
        // TODO: Implement set() method.
    }

    public function parent(): Ref|null
    {
        // TODO: Implement parent() method.
    }

    public function self(): Ref|null
    {
        // TODO: Implement self() method.
    }

    public function actor(): ActorInterface
    {
        // TODO: Implement actor() method.
    }

    public function actorSystem(): ActorSystem
    {
        // TODO: Implement actorSystem() method.
    }

    public function logger(): LoggerInterface
    {
        // TODO: Implement logger() method.
    }

    public function message(): mixed
    {
        if ($this->messageHandler !== null) {
            $handle = $this->messageHandler;

            return $handle();
        }

        return null;
    }

    public function messageHeader(): ReadonlyMessageHeaderInterface
    {
        // TODO: Implement messageHeader() method.
    }

    public function receive(MessageEnvelope|null $envelope): void
    {
        // TODO: Implement receive() method.
    }

    public function sender(): Ref|null
    {
        return new Ref(new PID([
            'address' => 'localhost',
            'id' => 'mock',
        ]));
    }

    public function send(Ref|null $pid, mixed $message): void
    {
        if ($this->sendHandler === null) {
            return;
        }

        $handle = $this->sendHandler;
        $handle($pid, $message);
    }

    public function request(Ref|null $pid, mixed $message): void
    {
        // TODO: Implement request() method.
    }

    public function requestWithCustomSender(Ref|null $pid, mixed $message, Ref|null $sender): void
    {
        if ($this->requestWithCustomSenderHandler === null) {
            return;
        }

        $handle = $this->requestWithCustomSenderHandler;
        $handle($pid, $message, $sender);
    }

    public function requestFuture(Ref|null $pid, mixed $message, int $duration): Future
    {
        // TODO: Implement requestFuture() method.
    }

    public function spawn(Props $props): Ref|null
    {
        // TODO: Implement spawn() method.
    }

    public function spawnPrefix(Props $props, string $prefix): Ref|null
    {
        // TODO: Implement spawnPrefix() method.
    }

    public function spawnNamed(Props $props, string $name): SpawnResult
    {
        // TODO: Implement spawnNamed() method.
    }

    public function stop(Ref|null $pid): void
    {
        // TODO: Implement stop() method.
    }

    public function stopFuture(Ref|null $pid): Future|null
    {
        // TODO: Implement stopFuture() method.
    }

    public function poison(Ref|null $pid): void
    {
        // TODO: Implement poison() method.
    }

    public function poisonFuture(Ref|null $pid): Future|null
    {
        // TODO: Implement poisonFuture() method.
    }
}
