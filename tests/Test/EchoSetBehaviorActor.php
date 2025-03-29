<?php

declare(strict_types=1);

namespace Test;

use Phluxor\ActorSystem\Behavior;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\ReceiveFunction;

class EchoSetBehaviorActor implements ActorInterface
{
    private Behavior $behavior;

    public function __construct()
    {
        $this->behavior = new Behavior();
        $this->behavior->become(
            new ReceiveFunction(
                fn (ContextInterface $context) => $this->one($context),
            ),
        );
    }

    public function receive(ContextInterface $context): void
    {
        $this->behavior->receive($context);
    }

    public function one(ContextInterface $context): void
    {
        if (! ($context->message() instanceof BehaviorMessage)) {
            return;
        }

        $this->behavior->become(
            new ReceiveFunction(
                fn (ContextInterface $context) => $this->other($context),
            ),
        );
    }

    public function other(ContextInterface $context): void
    {
        if (! ($context->message() instanceof EchoRequest)) {
            return;
        }

        $context->respond(new EchoResponse());
    }
}
