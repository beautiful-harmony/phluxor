<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

interface ContinuationFunctionInterface
{
    public function __invoke(): void;
}
