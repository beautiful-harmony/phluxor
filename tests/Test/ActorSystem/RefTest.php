<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Ref;
use PHPUnit\Framework\TestCase;
use Test\ProcessTrait;
use Test\VoidActor;

use function Swoole\Coroutine\run;

class RefTest extends TestCase
{
    use ProcessTrait;

    public function testShouldReturnDeadLetterProcess(): void
    {
        run(function (): void {
            go(function (): void {
                $actor = ActorSystem::create();
                $pid   = new ActorSystem\ProtoBuf\Pid();
                $pid->setAddress('localhost');
                $pid->setId('test');
                $r = (new Ref($pid))->ref($actor);
                $this->assertInstanceOf(
                    ActorSystem\DeadLetterProcess::class,
                    $r,
                );
            });
        });
    }

    public function testShouldReturnRefName(): void
    {
        run(function (): void {
            go(function (): void {
                $pid = new ActorSystem\ProtoBuf\Pid();
                $pid->setAddress('localhost');
                $pid->setId('test');
                $r = new Ref($pid);
                $this->assertSame('test', (string) $r);
            });
        });
    }

    public function testShouldReturnActorProcess(): void
    {
        run(function (): void {
            go(function (): void {
                $system = ActorSystem::create();
                $ref    = $system->root()->spawnNamed(
                    ActorSystem\Props::fromProducer(static fn () => new VoidActor()),
                    'test1',
                );
                $pid    = new ActorSystem\ProtoBuf\Pid([
                    'address' => 'nonhost',
                    'id' => 'test1',
                ]);
                $ref2   = new Ref($pid);
                $r      = $ref2->ref($system);
                $this->assertInstanceOf(ActorSystem\ActorProcess::class, $r);
                $this->assertTrue($ref->getRef()->equal($ref2));
            });
        });
    }

    public function testShouldSendUserMessage(): void
    {
        run(function (): void {
            go(function (): void {
                $system = ActorSystem::create();
                $this->spawnMockProcess(
                    $system,
                    'test1',
                    null,
                    function (Ref|null $pid, mixed $message) use (&$count): void {
                        $this->assertSame('hello', $message);
                        $count++;
                    },
                );
                $pid  = new ActorSystem\ProtoBuf\Pid([
                    'address' => 'nonhost',
                    'id' => 'test1',
                ]);
                $ref2 = new Ref($pid);
                $ref2->sendUserMessage($system, 'hello');
                $this->assertSame(1, $count);
            });
        });
    }

    public function testShouldSendSystemMessage(): void
    {
        run(function (): void {
            go(function (): void {
                $system = ActorSystem::create();
                $this->spawnMockProcess(
                    $system,
                    'test1',
                    function (Ref|null $pid, mixed $message) use (&$count): void {
                        $this->assertSame('hello', $message);
                        $count++;
                    },
                );
                $pid  = new ActorSystem\ProtoBuf\Pid([
                    'address' => 'nonhost',
                    'id' => 'test1',
                ]);
                $ref2 = new Ref($pid);
                $ref2->sendSystemMessage($system, 'hello');
                $this->assertSame(1, $count);
            });
        });
    }
}
