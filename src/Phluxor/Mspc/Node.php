<?php

declare(strict_types=1);

namespace Phluxor\Mspc;

class Node
{
    private Node|null $next = null;

    public function __construct(
        private mixed $val = null,
    ) {
    }

    public function getNext(): Node|null
    {
        return $this->next;
    }

    public function replaceNext(Node|null $next): void
    {
        $this->next = $next;
    }

    public function value(): mixed
    {
        return $this->val;
    }

    public function replaceValue(mixed $val): void
    {
        $this->val = $val;
    }
}
