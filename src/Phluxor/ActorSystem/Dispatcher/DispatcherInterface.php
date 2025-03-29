<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Dispatcher;

use Closure;

interface DispatcherInterface
{
    public function schedule(DispatcherFunctionInterface|Closure $fn): void;

    public function throughput(): int;
}
