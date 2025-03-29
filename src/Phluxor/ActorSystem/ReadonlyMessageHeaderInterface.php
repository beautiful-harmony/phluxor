<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

interface ReadonlyMessageHeaderInterface
{
    public function get(string $key): string|null;

    public function keys(): array;

    public function length(): int;

    public function toMap(): array;
}
