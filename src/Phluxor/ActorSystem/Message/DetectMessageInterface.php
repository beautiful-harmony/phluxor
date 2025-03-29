<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

interface DetectMessageInterface
{
    /**
     * match message
     */
    public function isMatch(): bool;
}
