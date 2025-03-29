<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\Value\ContextExtensionId;
use Phluxor\Value\ExtensionInterface;

interface ExtensionPartInterface
{
    public function get(ContextExtensionId $id): ExtensionInterface;

    public function set(ExtensionInterface $extension): void;
}
