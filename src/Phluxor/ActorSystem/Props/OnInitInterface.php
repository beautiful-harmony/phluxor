<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Props;

use Phluxor\ActorSystem\Context\ContextInterface;

interface OnInitInterface
{
    public function __invoke(ContextInterface $context): void;
}
