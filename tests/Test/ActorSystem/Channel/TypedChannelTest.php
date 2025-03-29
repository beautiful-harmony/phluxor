<?php

declare(strict_types=1);

namespace Test\ActorSystem\Channel;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Channel\TypedChannel;
use PHPUnit\Framework\TestCase;
use Test\EchoRequest;

use function is_string;
use function Swoole\Coroutine\run;

class TypedChannelTest extends TestCase
{
    public function testReceiveStringFromTypedChannel(): void
    {
        run(function (): void {
            go(function (): void {
                $system = ActorSystem::create();
                $c      = new TypedChannel(
                    $system,
                    static fn (mixed $message): bool => is_string($message),
                );
                $system->root()->send($c->getRef(), 'hello');
                $system->root()->send($c->getRef(), 'world');

                $this->assertEquals('hello', $c->result());
                $this->assertEquals('world', $c->result());
            });
        });
    }

    public function testReceiveEchoRequestFromTypedChannel(): void
    {
        run(function (): void {
            go(function (): void {
                $system = ActorSystem::create();
                $c      = new TypedChannel(
                    $system,
                    static fn (mixed $message): bool => $message instanceof EchoRequest,
                );

                $r = $system->root()->spawn(
                    ActorSystem\Props::fromFunction(
                        new ActorSystem\Message\ReceiveFunction(
                            static function (ActorSystem\Context\ContextInterface $context) use ($c): void {
                                $msg = $context->message();
                                $context->send($c->getRef(), $msg);
                            },
                        ),
                    ),
                );
                $system->root()->send($r, 'hello');
                $system->root()->send($r, new EchoRequest());

                $this->assertInstanceOf(EchoRequest::class, $c->result());
            });
        });
    }
}
