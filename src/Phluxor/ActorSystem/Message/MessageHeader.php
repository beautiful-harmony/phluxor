<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\ReadonlyMessageHeaderInterface;

use function array_keys;
use function count;

class MessageHeader implements ReadonlyMessageHeaderInterface
{
    /** @param string[] $header */
    public function __construct(
        private array $header = [],
    ) {
    }

    public function get(string $key): string|null
    {
        return $this->header[$key] ?? null;
    }

    public function set(string $key, string $value): void
    {
        $this->header[$key] = $value;
    }

    /** @return string[] */
    public function keys(): array
    {
        return array_keys($this->header);
    }

    public function length(): int
    {
        return count($this->header);
    }

    /** @return string[] */
    public function toMap(): array
    {
        return $this->header;
    }
}
