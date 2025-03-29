<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Strategy;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Child\RestartStatistics;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\SupervisorInterface;
use Phluxor\ActorSystem\SupervisorStrategyInterface;

final class RestartingStrategy implements SupervisorStrategyInterface
{
    public function handleFailure(
        ActorSystem $actorSystem,
        SupervisorInterface $supervisor,
        Ref $child,
        RestartStatistics $restartStatistics,
        mixed $reason,
        mixed $message,
    ): void {
        // always restart the actor
        $actorSystem->getEventStream()?->publish(
            new SupervisorEvent($child, $reason, ActorSystem\Directive::Restart),
        );
        $supervisor->restartChildren($child);
    }
}
