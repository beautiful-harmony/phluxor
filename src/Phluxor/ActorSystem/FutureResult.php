<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem\Exception\FutureTimeoutException;

final readonly class FutureResult
{
    public function __construct(
        private mixed $result,
        private FutureTimeoutException|null $error = null,
    ) {
    }

    public function value(): mixed
    {
        return $this->result;
    }

    public function error(): FutureTimeoutException|null
    {
        return $this->error;
    }
}
