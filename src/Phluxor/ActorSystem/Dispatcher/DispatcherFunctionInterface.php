<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Dispatcher;

interface DispatcherFunctionInterface
{
    public function __invoke(): void;
}
