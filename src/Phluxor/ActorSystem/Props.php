<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Context\SpawnerInterface;
use Phluxor\ActorSystem\Dispatcher\CoroutineDispatcher;
use Phluxor\ActorSystem\Dispatcher\DispatcherInterface;
use Phluxor\ActorSystem\Mailbox\MailboxInterface;
use Phluxor\ActorSystem\Mailbox\MailboxProducerInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\ContextDecoratorFunctionInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\ProducerInterface;
use Phluxor\ActorSystem\Message\ProducerWithActorSystemInterface;
use Phluxor\ActorSystem\Message\ReceiverFunctionInterface;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Props\OnInitInterface;
use Phluxor\ActorSystem\Props\ReceiverMiddlewareInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;

use function array_merge;
use function count;

class Props
{
    private MailboxProducerInterface|null $mailboxProducer = null;

    /** @var Closure(ContextInterface): ContextInterface|ContextDecoratorFunctionInterface|null */
    private Closure|ContextDecoratorFunctionInterface|null $contextDecoratorFunction = null;

    /** @var SenderMiddlewareInterface[] */
    private array $senderMiddleware = [];

    /** @var Closure(SenderInterface|ContextInterface, Ref, MessageEnvelope): void|SenderFunctionInterface|null */
    private Closure|SenderFunctionInterface|null $senderMiddlewareChain = null;
    private SupervisorStrategyInterface|null $supervisorStrategy        = null;
    private SupervisorStrategyInterface|null $guardianStrategy          = null;

    /** @var Closure[]|ActorSystem\Props\OnInitInterface[] */
    private array $onInit                        = [];
    private DispatcherInterface|null $dispatcher = null;

    /** @var Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null */
    private Closure|SpawnFunctionInterface|null $spawner = null;

    /** @var ActorSystem\Props\SpawnMiddlewareInterface[] */
    private array $spawnMiddleware = [];

    /** @var Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null */
    private Closure|SpawnFunctionInterface|null $spawnMiddlewareChain = null;

    /** @var ReceiverMiddlewareInterface[] */
    private array $receiverMiddleware = [];

    /** @var Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface|null */
    private Closure|ReceiverFunctionInterface|null $receiverMiddlewareChain = null;

    private readonly ActorSystem\Spawner\DefaultSpawner $defaultSpawner;

    /**
     * @param ProducerWithActorSystemInterface|Closure(ActorSystem): ActorInterface $producer
     * @param ActorSystem\Props\ContextDecoratorInterface[]                         $contextDecorator
     */
    public function __construct(
        private ProducerWithActorSystemInterface|Closure $producer,
        private array $contextDecorator = [],
    ) {
        $this->defaultSpawner = new ActorSystem\Spawner\DefaultSpawner();
    }

    public function getDispatcher(): DispatcherInterface
    {
        if ($this->dispatcher === null) {
            return $this->defaultDispatcher();
        }

        return $this->dispatcher;
    }

    public function withProduceMailbox(MailboxProducerInterface $mailboxProducer): Props
    {
        $this->mailboxProducer = $mailboxProducer;

        return $this;
    }

    public function produceMailbox(): MailboxInterface
    {
        if ($this->mailboxProducer === null) {
            $unbounded = new ActorSystem\Mailbox\Unbounded();

            return $unbounded();
        }

        $mailProducer = $this->mailboxProducer;

        return $mailProducer();
    }

    /** @return Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface */
    public function getSpawner(): Closure|SpawnFunctionInterface
    {
        if ($this->spawner === null) {
            return $this->defaultSpawner;
        }

        return $this->spawner;
    }

    public function spawn(
        ActorSystem $actorSystem,
        string $name,
        SpawnerInterface $spawner,
    ): SpawnResult {
        return $this->getSpawner()($actorSystem, $name, $this, $spawner);
    }

    public function producer(ActorSystem $system): ActorInterface
    {
        $producer = $this->producer;

        return $producer($system);
    }

    protected function defaultDispatcher(): DispatcherInterface
    {
        return new CoroutineDispatcher(300);
    }

    /**
     * default spawner for creating actors
     */
    public function getDefaultSpawner(): ActorSystem\Spawner\DefaultSpawner
    {
        return $this->defaultSpawner;
    }

    public function initialize(Props $props, ActorContext $ctx): void
    {
        if (count($props->onInit) === 0) {
            return;
        }

        foreach ($props->onInit as $init) {
            $init($ctx);
        }
    }

    /** @return Closure(SenderInterface|ContextInterface, Ref, MessageEnvelope): void|SenderFunctionInterface|null */
    public function senderMiddlewareChain(): Closure|SenderFunctionInterface|null
    {
        return $this->senderMiddlewareChain;
    }

    /** @return Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null */
    public function spawnMiddlewareChain(): Closure|SpawnFunctionInterface|null
    {
        return $this->spawnMiddlewareChain;
    }

    /**
     * @deprecated
     *
     * @return Closure(ContextInterface): ContextInterface|ContextDecoratorFunctionInterface|null
     */
    public function getContextDecoratorChain(): Closure|ContextDecoratorFunctionInterface|null
    {
        if ($this->contextDecoratorFunction === null) {
            return new ActorSystem\Message\DefaultContextDecorator();
        }

        return $this->contextDecoratorFunction;
    }

    public function contextDecoratorChain(): Closure|ContextDecoratorFunctionInterface|null
    {
        return $this->contextDecoratorFunction;
    }

    public function getGuardianStrategy(): SupervisorStrategyInterface|null
    {
        return $this->guardianStrategy;
    }

    public function getSupervisorStrategy(): SupervisorStrategyInterface
    {
        if ($this->supervisorStrategy === null) {
            $this->supervisorStrategy = new ActorSystem\Strategy\OneForOneStrategy(
                10,
                new DateInterval('PT10S'),
                static fn ($reason) => Directive::Restart,
            );
        }

        return $this->supervisorStrategy;
    }

    /**
     * @param Closure(ContextInterface): void|OnInitInterface ...$init
     *
     * @return Closure(Props): void
     */
    public static function withOnInit(Closure|OnInitInterface ...$init): Closure
    {
        return static function (Props $props) use ($init): void {
            $props->onInit = $init;
        };
    }

    /**
     * @param ProducerInterface|Closure(): ActorInterface $producer
     *
     * @return Closure(Props): void
     */
    public static function withProducer(ProducerInterface|Closure $producer): Closure
    {
        return static function (Props $props) use ($producer): void {
            $props->producer = static fn (ActorSystem $system) => $producer();
        };
    }

    /** @return Closure(Props): void */
    public static function withDispatcher(DispatcherInterface|null $dispatcher): Closure
    {
        return static function (Props $props) use ($dispatcher): void {
            $props->dispatcher = $dispatcher;
        };
    }

    /** @return Closure(Props): void */
    public static function withMailboxProducer(MailboxProducerInterface|null $mailboxProducer): Closure
    {
        return static function (Props $props) use ($mailboxProducer): void {
            $props->mailboxProducer = $mailboxProducer;
        };
    }

    /** @return Closure(Props): void */
    public static function withContextDecorator(
        ActorSystem\Props\ContextDecoratorInterface ...$contextDecorator,
    ): Closure {
        return static function (Props $props) use ($contextDecorator): void {
            $props->contextDecorator = array_merge($props->contextDecorator, $contextDecorator);

            $props->contextDecoratorFunction = makeContextDecoratorChain(
                $props->contextDecorator,
                static fn ($ctx) => $ctx,
            );
        };
    }

    /** @return Closure(Props): void */
    public static function withGuardian(SupervisorStrategyInterface|null $strategy): Closure
    {
        return static function (Props $props) use ($strategy): void {
            $props->guardianStrategy = $strategy;
        };
    }

    /** @return Closure(Props): void */
    public static function withSupervisor(SupervisorStrategyInterface|null $strategy): Closure
    {
        return static function (Props $props) use ($strategy): void {
            $props->supervisorStrategy = $strategy;
        };
    }

    /** @return Closure(Props): void */
    public static function withReceiverMiddleware(
        ReceiverMiddlewareInterface ...$receiverMiddleware,
    ): Closure {
        return static function (Props $props) use ($receiverMiddleware): void {
            $props->receiverMiddleware = array_merge($props->receiverMiddleware, $receiverMiddleware);

            $props->receiverMiddlewareChain = makeReceiverMiddlewareChain(
                $props->receiverMiddleware,
                static fn ($ctx, $message) => $ctx->receive($message),
            );
        };
    }

    /** @return Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface|null */
    public function getReceiverMiddlewareChain(): Closure|ReceiverFunctionInterface|null
    {
        return $this->receiverMiddlewareChain;
    }

    /** @return Closure(Props): void */
    public static function withSenderMiddleware(
        ActorSystem\Props\SenderMiddlewareInterface ...$senderMiddleware,
    ): Closure {
        return static function (Props $props) use ($senderMiddleware): void {
            $props->senderMiddleware = array_merge($props->senderMiddleware, $senderMiddleware);

            $props->senderMiddlewareChain = makeSenderMiddlewareChain(
                $props->senderMiddleware,
                static function (SenderInterface|ContextInterface $ctx, Ref $pid, MessageEnvelope $envelope): void {
                    $pid->sendUserMessage(
                        $ctx->actorSystem(),
                        $envelope->getMessage(),
                    );
                },
            );
        };
    }

    /**
     * @param Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null $spawn
     *
     * @return Closure(Props): void
     */
    public static function withSpawnFunc(
        Closure|SpawnFunctionInterface|null $spawn,
    ): Closure {
        return static function (Props $props) use ($spawn): void {
            $props->spawner = $spawn;
        };
    }

    /** @return Closure(Props): void */
    public static function withFunc(
        ActorSystem\Message\ReceiveFunction $f,
    ): Closure {
        return static function (Props $props) use ($f): void {
            $props->producer = static fn (ActorSystem $system) => $f;
        };
    }

    public static function withSpawnMiddleware(
        ActorSystem\Props\SpawnMiddlewareInterface ...$middleware,
    ): Closure {
        return static function (Props $props) use ($middleware): void {
            $props->spawnMiddleware      = array_merge($props->spawnMiddleware, $middleware);
            $props->spawnMiddlewareChain = makeSpawnMiddlewareChain(
                $props->spawnMiddleware,
                static function (ActorSystem $actorSystem, string $id, Props $props, SpawnerInterface $context): SpawnResult {
                    if ($props->spawner === null) {
                        $defaultSpawner = $props->defaultSpawner;

                        return $defaultSpawner($actorSystem, $id, $props, $context);
                    }

                    $spawner = $props->spawner;

                    return $spawner($actorSystem, $id, $props, $context);
                },
            );
        };
    }

    /**
     * @param Closure(Props): void ...$options
     *
     * @return $this
     */
    public function configure(Closure ...$options): Props
    {
        foreach ($options as $option) {
            $option($this);
        }

        return $this;
    }

    /**
     * creates a props with the given actor producer assigned.
     *
     * @param ProducerInterface|Closure(): ActorInterface $producer
     * @param Closure(Props): void                        ...$options
     */
    public static function fromProducer(
        ProducerInterface|Closure $producer,
        Closure ...$options,
    ): Props {
        $props = new Props(
            producer: static fn (ActorSystem $system) => $producer(),
            contextDecorator: [],
        );
        $props->configure(...$options);

        return $props;
    }

    /**
     * creates a props with the given actor producer assigned.
     *
     * @param ProducerWithActorSystemInterface|Closure(ActorSystem): ActorInterface $producer
     * @param Closure(Props): void                                                  ...$options
     */
    public static function fromProducerWithActorSystem(
        ProducerWithActorSystemInterface|Closure $producer,
        Closure ...$options,
    ): Props {
        $props = new Props(
            producer: $producer,
            contextDecorator: [],
        );
        $props->configure(...$options);

        return $props;
    }

    /**
     * creates a props with the given receive func assigned as the actor producer.
     *
     * @param Closure(Props): void ...$options
     */
    public static function fromFunction(
        ActorSystem\Message\ReceiveFunction $f,
        Closure ...$options,
    ): Props {
        return static::fromProducer(static fn (): ActorInterface => $f, ...$options);
    }

    /** @param Closure(Props): void ...$options */
    public function clone(Closure ...$options): Props
    {
        $props = static::fromProducerWithActorSystem(
            $this->producer,
            static::withDispatcher($this->dispatcher),
            static::withMailboxProducer($this->mailboxProducer),
            static::withContextDecorator(...$this->contextDecorator),
            static::withGuardian($this->guardianStrategy),
            static::withSupervisor($this->supervisorStrategy),
            static::withReceiverMiddleware(...$this->receiverMiddleware),
            static::withSenderMiddleware(...$this->senderMiddleware),
            static::withSpawnFunc($this->spawner),
            static::withSpawnMiddleware(...$this->spawnMiddleware),
            static::withOnInit(...$this->onInit),
        );
        $props->configure(...$options);

        return $props;
    }
}
