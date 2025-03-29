<?php

declare(strict_types=1);

namespace Phluxor\Value;

interface ExtensionInterface
{
    public function extensionID(): ContextExtensionId;
}
