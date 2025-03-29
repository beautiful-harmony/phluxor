<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

/**
 * AddressResolverInterface is used to resolve remote actors
 */
interface AddressResolverInterface
{
    /**
     * Resolves the address to a Ref
     */
    public function __invoke(Ref|null $pid): ProcessRegistryResult;
}
