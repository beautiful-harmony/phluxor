<?php

declare(strict_types=1);

namespace Test\ActorSystem\Middleware;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Middleware\Propagator\MiddlewarePropagation;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Swoole\Lock;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

class MiddlewarePropagatorTest extends TestCase
{
    private int $spawnCounter                                  = 0;
    private ActorSystem\Message\ReceiveFunction|null $starFunc = null;

    public function testMiddlewarePropagator(): void
    {
        run(function (): void {
            go(function (): void {
                $lock       = new Lock(Lock::MUTEX);
                $system     = ActorSystem::create();
                $propagator = new MiddlewarePropagation();
                $propagator->setItselfForwarded()
                    ->setSpawnMiddleware(
                        $this->spawnMiddleware($lock),
                    );
                $rootContext = new ActorSystem\RootContext($system);
                $rootContext->withSpawnMiddleware($propagator->spawnMiddleware());
                $root = $rootContext->spawn($this->start(5));
                $rootContext->stopFuture($root)->wait();
                $this->assertSame(5, $this->spawnCounter);
            });
        });
    }

    public function spawnMiddleware(Lock $lock): SpawnMiddlewareInterface
    {
        return new TestMiddleware($this->spawnCounter, $lock);
    }

    public function start(int $input): ActorSystem\Props
    {
        return ActorSystem\Props::fromFunction(
            new ActorSystem\Message\ReceiveFunction(
                function (ContextInterface $c) use ($input): void {
                    $message = $c->message();
                    if (! ($message instanceof ActorSystem\Message\Started)) {
                        return;
                    }

                    if ($input <= 0) {
                        return;
                    }

                    $c->spawn($this->start($input - 1));
                },
            ),
        );
    }
}
