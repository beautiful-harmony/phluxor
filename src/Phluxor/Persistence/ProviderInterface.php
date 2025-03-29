<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

/**
 * ProviderInterface is the abstraction used for persistence
 */
interface ProviderInterface
{
    public function getState(): ProviderStateInterface;
}
