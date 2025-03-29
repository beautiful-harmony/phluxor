<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\SpawnResult;

interface SpawnPartInterface
{
    /**
     * starts a new child actor based on props and named with a unique id
     */
    public function spawn(Props $props): Ref|null;

    /**
     * starts a new child actor based on props and named using a prefix followed by a unique id
     */
    public function spawnPrefix(Props $props, string $prefix): Ref|null;

    /**
     * starts a new child actor based on props and named using the specified name
     */
    public function spawnNamed(Props $props, string $name): SpawnResult;
}
