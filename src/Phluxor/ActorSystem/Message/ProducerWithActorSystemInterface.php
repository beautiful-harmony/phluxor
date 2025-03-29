<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem;

interface ProducerWithActorSystemInterface
{
    public function __invoke(ActorSystem $system): ActorInterface;
}
