<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

interface ProducerInterface
{
    public function __invoke(): ActorInterface;
}
