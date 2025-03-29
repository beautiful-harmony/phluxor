<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Google\Protobuf\Internal\Message;

interface SnapshotStoreInterface
{
    public function getSnapshot(
        string $actorName,
    ): SnapshotResult;

    public function persistenceSnapshot(
        string $actorName,
        int $snapshotIndex,
        Message $snapshot,
    ): void;
}
