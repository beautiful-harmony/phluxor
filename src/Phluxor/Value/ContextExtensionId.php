<?php

declare(strict_types=1);

namespace Phluxor\Value;

use Swoole\Atomic;

final class ContextExtensionId
{
    private int $id;

    public function __construct(int $value = 1)
    {
        $id       = new Atomic($value);
        $this->id = $id->add();
    }

    public function value(): int
    {
        return $this->id;
    }
}
