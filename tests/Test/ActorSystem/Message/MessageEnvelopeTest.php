<?php

declare(strict_types=1);

namespace Test\ActorSystem\Message;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ReceiveFunction;
use Phluxor\ActorSystem\Props;
use PHPUnit\Framework\TestCase;

use function is_string;
use function Swoole\Coroutine\run;

class MessageEnvelopeTest extends TestCase
{
    public function testNormalMessageEmptyHeader(): void
    {
        run(function (): void {
            $system = ActorSystem::create();
            go(function (ActorSystem $system): void {
                $pid    = $system->root()->spawn(
                    Props::fromFunction(
                        new ReceiveFunction(static function (ContextInterface $context): void {
                            if (! is_string($context->message())) {
                                return;
                            }

                            $context->respond($context->messageHeader()->keys());
                        }),
                    ),
                );
                $future = $system->root()->requestFuture($pid, 'hello', 1);
                $this->assertCount(0, $future->result()->value());
                $this->assertNull($future->result()->error());
                $system->root()->stop($pid);
            }, $system);
        });
    }
}
