<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use DateInterval;
use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\ReenterAfterInterface;
use Phluxor\ActorSystem\Ref;
use Throwable;

interface BasePartInterface
{
    /**
     * returns the current timeout
     */
    public function receiveTimeout(): DateInterval;

    /**
     * returns a slice of the actors children
     *
     * @return Ref[]
     */
    public function children(): array;

    /**
     * sends a response to the current `Sender`
     */
    public function respond(mixed $response): void;

    /**
     * stashes the current message on a stack for reprocessing when the actor restarts
     */
    public function stash(): void;

    /**
     * registers the actor as a monitor for the specified Ref
     */
    public function watch(Ref $pid): void;

    /**
     * unregisters the actor as a monitor for the specified Ref
     */
    public function unwatch(Ref $pid): void;

    public function setReceiveTimeout(DateInterval $dateInterval): void;

    public function cancelReceiveTimeout(): void;

    /**
     * forwards current message to the given Ref
     */
    public function forward(Ref $pid): void;

    /**
     * Executes the given Future and reenters the current method after the Future has completed.
     *
     * @param Future                $future       The Future to execute.
     * @param ReenterAfterInterface $reenterAfter The ReenterAfterInterface object that defines how the current method should be reentered.
     *
     * @throws Throwable If an exception occurs while executing the Future.
     */
    public function reenterAfter(Future $future, ReenterAfterInterface $reenterAfter): void;
}
