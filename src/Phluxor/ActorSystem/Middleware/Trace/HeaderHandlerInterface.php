<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware\Trace;

use Exception;
use Throwable;

interface HeaderHandlerInterface
{
    /** @return Exception|null */
    public function __invoke(string $key, string $val): Throwable|null;
}
