<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Props;

use Closure;
use Phluxor\ActorSystem\SpawnFunctionInterface;

interface SpawnMiddlewareInterface
{
    public function __invoke(
        Closure|SpawnFunctionInterface $next,
    ): Closure|SpawnFunctionInterface;
}
