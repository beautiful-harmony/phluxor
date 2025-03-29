<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Spawner;

use OpenTelemetry\API\Metrics\ObserverInterface;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\SpawnerInterface;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\SpawnResult;
use Phluxor\Metrics\ActorMetrics;
use Phluxor\Metrics\PhluxorMetrics;

use function sprintf;

class DefaultSpawner implements ActorSystem\SpawnFunctionInterface
{
    public function __invoke(
        ActorSystem $actorSystem,
        string $id,
        Props $props,
        SpawnerInterface $parentContext,
    ): SpawnResult {
        $context = new ActorSystem\ActorContext(
            actorSystem: $actorSystem,
            props: $props,
            parent: $parentContext->self(),
        );
        $context->incarnateActor();
        $mailbox = $props->produceMailbox();

        $this->prepareMailboxMetrics($actorSystem, $mailbox, $context);

        $dispatcher = $props->getDispatcher();
        $process    = new ActorSystem\ActorProcess($mailbox);
        $addResult  = $actorSystem->getProcessRegistry()->add($process, $id);
        if (! $addResult->isAdded()) {
            return new SpawnResult(
                $addResult->getRef(),
                new ActorSystem\Exception\NameExistsException(
                    sprintf('Actor with id %s already exists', $id),
                ),
            );
        }

        $context->setSelf($addResult->getRef());
        $props->initialize($props, $context);
        $mailbox->registerHandlers($context, $dispatcher);
        $mailbox->postSystemMessage(new ActorSystem\Message\Started());
        $mailbox->start();

        return new SpawnResult(
            $addResult->getRef(),
            null,
        );
    }

    public function prepareMailboxMetrics(
        ActorSystem $actorSystem,
        ActorSystem\Mailbox\MailboxInterface $mailbox,
        ActorSystem\ActorContext $context,
    ): void {
        if ($actorSystem->config()->metricsProvider() === null) {
            return;
        }

        $id = $actorSystem->metrics()?->extensionID();
        if ($id === null) {
            return;
        }

        $metricsSystem = $actorSystem->extensions()->get($id);
        if (! ($metricsSystem instanceof ActorSystem\Metrics)) {
            return;
        }

        if (! $metricsSystem->isEnabled()) {
            return;
        }

        $instruments = $metricsSystem->metrics()->find(PhluxorMetrics::INTERNAL_ACTOR_METRICS);
        if (! ($instruments instanceof ActorMetrics)) {
            return;
        }

        $metricsSystem->prepareMailboxLengthGauge(
            static fn (ObserverInterface $observer) => $observer->observe(
                $mailbox->userMessageCount(),
                $metricsSystem->commonLabels($context),
            ),
        );
    }
}
