<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use function ord;
use function strlen;

class ConcurrentMap
{
    /** @var ConcurrentMapShared[] */
    private array $map = [];

    private const int SHARD_COUNT = 32;

    public function __construct()
    {
        for ($i = 0; $i < self::SHARD_COUNT; $i++) {
            $this->map[$i] = new ConcurrentMapShared();
        }
    }

    public function getShard(string $key): ConcurrentMapShared
    {
        $shardIndex = $this->fnv32($key) % self::SHARD_COUNT;

        return $this->map[$shardIndex];
    }

    public function setIfAbsent(string $key, mixed $value): bool
    {
        $shard = $this->getShard($key);
        $shard->lock();
        $set = false;
        if (! $shard->offsetExists($key)) {
            $shard->offsetSet($key, $value);
            $set = true;
        }

        $shard->unlock();

        return $set;
    }

    public function pop(string $key): ConcurrentMapResult
    {
        $shard = $this->getShard($key);
        $shard->lock();
        $exists = $shard->offsetExists($key);
        $value  = null;
        if ($exists) {
            $value = $shard->offsetGet($key);
        }

        $shard->offsetUnset($key);
        $shard->unlock();

        return new ConcurrentMapResult($value, $exists);
    }

    public function get(string $key): ConcurrentMapResult
    {
        $shard = $this->getShard($key);
        $shard->lock();
        $exists = $shard->offsetExists($key);
        $value  = null;
        if ($exists) {
            $value = $shard->offsetGet($key);
        }

        $shard->unlock();

        return new ConcurrentMapResult($value, $exists);
    }

    public function fnv32(string $key): int
    {
        $hash    = 0x811c9dc5;     // FNV offset basis (32bit)
        $prime32 = 0x01000193; // FNV prime (32bit)
        $len     = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            $hash ^= ord($key[$i]);
            $hash  = $hash * $prime32 & 0xffffffff;
        }

        return $hash;
    }
}
