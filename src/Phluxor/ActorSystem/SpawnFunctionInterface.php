<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;

interface SpawnFunctionInterface
{
    public function __invoke(
        ActorSystem $actorSystem,
        string $id,
        Props $props,
        ActorSystem\Context\SpawnerInterface $parentContext,
    ): SpawnResult;
}
