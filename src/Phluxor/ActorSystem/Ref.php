<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;

class Ref
{
    private ProcessInterface|null $process = null;

    public function __construct(
        private readonly ActorSystem\ProtoBuf\Pid $pid,
    ) {
    }

    public function registerProcess(ProcessInterface $process): void
    {
        $this->process = $process;
    }

    public function resetProcess(): void
    {
        $this->process = null;
    }

    public function ref(ActorSystem $actorSystem): ProcessInterface|null
    {
        if ($this->process !== null) {
            if (! ($this->process instanceof ActorProcess) || $this->process->dead()->get() !== 1) {
                return $this->process;
            }

            $this->process = null;
        }

        $result = $actorSystem->getProcessRegistry()->get($this);
        if ($result->isProcess()) {
            $this->process = $result->getProcess();
        }

        return $result->getProcess();
    }

    public function sendUserMessage(ActorSystem $actorSystem, mixed $message): void
    {
        $this->ref($actorSystem)?->sendUserMessage($this, $message);
    }

    public function sendSystemMessage(ActorSystem $actorSystem, mixed $message): void
    {
        $this->ref($actorSystem)?->sendSystemMessage($this, $message);
    }

    public function protobufPid(): ActorSystem\ProtoBuf\Pid
    {
        return $this->pid;
    }

    public function equal(Ref|null $other): bool
    {
        if ($other === null) {
            return false;
        }

        return $this->pid->getId() === $other->pid->getId()
            && $this->pid->getAddress() === $other->pid->getAddress()
            && $this->pid->getRequestId() === $other->pid->getRequestId();
    }

    public function __toString(): string
    {
        return $this->pid->getId();
    }
}
