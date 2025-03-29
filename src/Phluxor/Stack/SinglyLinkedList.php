<?php

declare(strict_types=1);

namespace Phluxor\Stack;

use RuntimeException;

class SinglyLinkedList
{
    private int $size = 0;

    public function __construct(
        public Node|null $head = null,
        public Node|null $tail = null,
    ) {
    }

    public function prepend(mixed $data): void
    {
        $node         = new Node($data, $this->head);
        $this->head   = $node;
        $this->tail ??= $node;
        $this->size++;
    }

    public function push(mixed $data): void
    {
        $this->prepend($data);
    }

    public function get(int $index = 0): mixed
    {
        $node = $this->head;
        for ($i = 0; $i < $index; $i++) {
            if (! $node) {
                break;
            }

            $node = $node->getNext();
        }

        if (! $node) {
            throw new RuntimeException('Index out of bounds');
        }

        return $node->getValue();
    }

    public function remove(int $index = 0): void
    {
        $node = $this->head;
        $prev = null;
        for ($i = 0; $i < $index; $i++) {
            if (! $node) {
                break;
            }

            $prev = $node;
            $node = $node->getNext();
        }

        if (! $node) {
            throw new RuntimeException('Index out of bounds');
        }

        if ($prev) {
            $prev->replaceValue($node->getNext());
        } else {
            $this->head = $node->getNext();
        }

        $this->size--;
    }

    public function pop(): mixed
    {
        $node = $this->get(0);
        $this->remove(0);

        return $node;
    }

    public function length(): int
    {
        return $this->size;
    }
}
