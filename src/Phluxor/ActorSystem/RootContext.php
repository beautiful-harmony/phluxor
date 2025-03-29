<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Context\SpawnerInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;
use Psr\Log\LoggerInterface;

class RootContext implements
    ActorSystem\Context\SpawnerInterface,
    ActorSystem\Context\SenderInterface,
    ActorSystem\Context\StopperPartInterface
{
    /** @var Closure(SenderInterface, Ref, MessageEnvelope): void|SenderFunctionInterface|null */
    private Closure|SenderFunctionInterface|null $senderMiddleware;

    /** @var Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null */
    private Closure|SpawnFunctionInterface|null $spawnMiddleware = null;
    private SupervisorStrategyInterface|null $guardianStrategy   = null;

    /**
     * @param string[]                              $headers
     * @param Closure[]|SenderMiddlewareInterface[] $senderMiddlewares
     */
    public function __construct(
        private readonly ActorSystem $actorSystem,
        private array $headers = [],
        array $senderMiddlewares = [],
    ) {
        $this->senderMiddleware = makeSenderMiddlewareChain(
            $senderMiddlewares,
            new ActorSystem\Middleware\DefaultRootContextSender($this->actorSystem),
        );
    }

    public function copy(): RootContext
    {
        return $this;
    }

    public function actorSystem(): ActorSystem
    {
        return $this->actorSystem;
    }

    public function logger(): LoggerInterface
    {
        return $this->actorSystem->getLogger();
    }

    public function withHeader(string $key, string $value): RootContext
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /** @return $this */
    public function withSenderMiddleware(SenderMiddlewareInterface ...$middleware): RootContext
    {
        $this->senderMiddleware = makeSenderMiddlewareChain(
            $middleware,
            new ActorSystem\Middleware\DefaultRootContextSender($this->actorSystem),
        );

        return $this;
    }

    /** @return $this */
    public function withSpawnMiddleware(Closure|SpawnMiddlewareInterface ...$middleware): RootContext
    {
        $this->spawnMiddleware = makeSpawnMiddlewareChain(
            $middleware,
            function (
                ActorSystem $actorSystem,
                string $id,
                Props $props,
            ): SpawnResult {
                return $props->spawn($actorSystem, $id, $this);
            },
        );

        return $this;
    }

    /** @return $this */
    public function withGuardian(SupervisorStrategyInterface $supervisorStrategy): RootContext
    {
        $this->guardianStrategy = $supervisorStrategy;

        return $this;
    }

    public function parent(): Ref|null
    {
        return null;
    }

    public function self(): Ref|null
    {
        if ($this->guardianStrategy !== null) {
            return $this->actorSystem->getGuardiansValue()->getGuardianRef($this->guardianStrategy);
        }

        return null;
    }

    public function sender(): Ref|null
    {
        return null;
    }

    public function actor(): ActorInterface
    {
        throw new ActorSystem\Exception\RootContextActorException(
            'RootContext cannot be used as an actor',
        );
    }

    public function message(): mixed
    {
        return null;
    }

    public function messageHeader(): ReadonlyMessageHeaderInterface
    {
        return new ActorSystem\Message\MessageHeader($this->headers);
    }

    public function send(Ref|null $pid, mixed $message): void
    {
        $this->sendUserMessage($pid, $message);
    }

    public function request(Ref|null $pid, mixed $message): void
    {
        $this->sendUserMessage($pid, $message);
    }

    public function requestWithCustomSender(Ref|null $pid, mixed $message, Ref|null $sender): void
    {
        $env = new MessageEnvelope(
            header: null,
            message: $message,
            sender: $sender,
        );
        $this->sendUserMessage($pid, $env);
    }

    public function requestFuture(Ref|null $pid, mixed $message, int $duration): Future
    {
        $future = Future::create($this->actorSystem, $duration);
        $env    = new MessageEnvelope(
            header: null,
            message: $message,
            sender: $future->pid(),
        );
        $this->sendUserMessage($pid, $env);

        return $future;
    }

    private function sendUserMessage(Ref|null $pid, mixed $envelope): void
    {
        if ($this->senderMiddleware !== null) {
            $call = $this->senderMiddleware;
            $call($this, $pid, $envelope);
        } else {
            $pid?->sendUserMessage($this->actorSystem, $envelope);
        }
    }

    /**
     * starts a new actor based on props and named with a unique id.
     */
    public function spawn(Props $props): Ref|null
    {
        $result = $this->spawnNamed($props, $this->actorSystem->getProcessRegistry()->nextId());
        if ($result->isError() !== null) {
            throw $result->isError();
        }

        return $result->getRef();
    }

    /**
     * starts a new actor based on props and named with a unique id.
     */
    public function spawnPrefix(Props $props, string $prefix): Ref|null
    {
        $result = $this->spawnNamed($props, $prefix . $this->actorSystem->getProcessRegistry()->nextId());
        if ($result->isError() !== null) {
            throw $result->isError();
        }

        return $result->getRef();
    }

    /**
     * starts a new actor based on props and named using the specified name
     */
    public function spawnNamed(Props $props, string $name): SpawnResult
    {
        $rootContext = $this;
        if ($props->getGuardianStrategy() !== null) {
            $rootContext = $this->copy()->withGuardian($props->getGuardianStrategy());
        }

        if ($rootContext->spawnMiddleware !== null) {
            $spawnMiddleware = $this->spawnMiddleware;

            return $spawnMiddleware($this->actorSystem, $name, $props, $rootContext);
        }

        return $props->spawn($this->actorSystem, $name, $rootContext);
    }

    public function stop(Ref|null $pid): void
    {
        if ($pid === null) {
            return;
        }

        $pid->ref($this->actorSystem)?->stop($pid);
    }

    public function stopFuture(Ref|null $pid): Future|null
    {
        $future = Future::create($this->actorSystem, 10);
        if ($pid !== null) {
            $pid->sendSystemMessage(
                $this->actorSystem,
                new ActorSystem\ProtoBuf\Watch([
                    'watcher' => $future->pid()?->protobufPid(),
                ]),
            );
            $this->stop($pid);

            return $future;
        }

        return null;
    }

    public function poison(Ref|null $pid): void
    {
        $pid?->sendUserMessage($this->actorSystem(), new ActorSystem\ProtoBuf\PoisonPill());
    }

    public function poisonFuture(Ref|null $pid): Future|null
    {
        $future = Future::create($this->actorSystem, 10);
        if ($pid !== null) {
            $pid->sendSystemMessage(
                $this->actorSystem(),
                new ActorSystem\ProtoBuf\Watch([
                    'watcher' => $future->pid()?->protobufPid(),
                ]),
            );
            $this->poison($pid);

            return $future;
        }

        return null;
    }
}
