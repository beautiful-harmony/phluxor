<?php

declare(strict_types=1);

namespace Phluxor\Stack;

class Node
{
    public function __construct(
        private mixed $value,
        private Node|null $next = null,
    ) {
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getNext(): Node|null
    {
        return $this->next;
    }

    public function replaceValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function replaceNext(Node|null $next): void
    {
        $this->next = $next;
    }
}
