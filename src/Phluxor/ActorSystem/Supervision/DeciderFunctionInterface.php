<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Supervision;

use Phluxor\ActorSystem\Directive;

interface DeciderFunctionInterface
{
    public function __invoke(mixed $reason): Directive;
}
