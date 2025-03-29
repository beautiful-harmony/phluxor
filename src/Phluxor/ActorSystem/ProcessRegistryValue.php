<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;
use Swoole\Atomic;

use function array_fill;
use function array_slice;
use function implode;

class ProcessRegistryValue
{
    private const string DIGITS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~+';

    private string $address;

    /** @var AddressResolverInterface[] $remoteHandlers */
    private array $remoteHandlers = [];

    public function __construct(
        private readonly ActorSystem $actorSystem,
        private readonly SliceMap $localPIDs = new SliceMap(),
        private readonly Atomic $sequenceID = new Atomic(0),
    ) {
        $this->address = ActorSystem::LOCAL_ADDRESS;
    }

    public function registerAddressResolver(AddressResolverInterface $handler): void
    {
        $this->remoteHandlers[] = $handler;
    }

    public function uint64ToId(int $u): string
    {
        $digits = $this::DIGITS;
        $buf    = array_fill(0, 13, 0);
        $i      = 13;
        while ($u >= 64) {
            $i--;
            $buf[$i] = $digits[$u & 0x3f];
            $u     >>= 6;
        }

        $i--;
        $buf[$i] = $digits[$u];
        $i--;
        $buf[$i] = '$';

        return implode('', array_slice($buf, $i));
    }

    public function nextId(): string
    {
        return $this->uint64ToId($this->sequenceID->add());
    }

    public function add(ProcessInterface $process, string $id): ProcessRegistryAddResult
    {
        $bucket = $this->localPIDs->getBucket($id);
        $pid    = new Ref(new ActorSystem\ProtoBuf\Pid());
        $pid->protobufPid()->setAddress($this->address);
        $pid->protobufPid()->setId($id);

        return new ProcessRegistryAddResult($pid, $bucket->setIfAbsent($id, $process));
    }

    /**
     * remove a process from the registry
     */
    public function remove(Ref $pid): void
    {
        $bucket = $this->localPIDs->getBucket($pid->protobufPid()->getId());
        $ref    = $bucket->pop($pid->protobufPid()->getId());
        $value  = $ref->value;
        if (! ($value instanceof ActorProcess)) {
            return;
        }

        $value->dead()->set(1);
    }

    public function get(Ref|null $pid): ProcessRegistryResult
    {
        if ($pid === null) {
            return new ProcessRegistryResult($this->actorSystem->getDeadLetter(), false);
        }

        if ($pid->protobufPid()->getAddress() !== ActorSystem::LOCAL_ADDRESS) {
            if ($pid->protobufPid()->getAddress() !== $this->address) {
                foreach ($this->remoteHandlers as $handler) {
                    $result = $handler($pid);
                    if ($result->isProcess()) {
                        return new ProcessRegistryResult($result->getProcess(), true);
                    }
                }
            }
        }

        $bucket = $this->localPIDs->getBucket($pid->protobufPid()->getId());
        $value  = $bucket->get($pid->protobufPid()->getId());
        if (! $value->exists) {
            return new ProcessRegistryResult($this->actorSystem->getDeadLetter(), false);
        }

        return new ProcessRegistryResult($value->value, true);
    }

    public function getLocal(string $id): ProcessRegistryResult
    {
        $bucket = $this->localPIDs->getBucket($id);
        $value  = $bucket->get($id);
        if (! $value->exists) {
            return new ProcessRegistryResult($this->actorSystem->getDeadLetter(), false);
        }

        return new ProcessRegistryResult($value->value, true);
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }
}
