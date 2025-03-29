<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem\ConcurrentMapShared;
use Phluxor\ActorSystem\SliceMap;
use PHPUnit\Framework\TestCase;

use function go;
use function Swoole\Coroutine\run;

class SliceMapTest extends TestCase
{
    public function testSliceMapShared(): void
    {
        run(function (): void {
            go(function (): void {
                $map = new SliceMap();
                $cm  = $map->getBucket('a');
                $this->assertInstanceOf(ConcurrentMapShared::class, $cm->getShard('a'));
            });
        });
    }

    public function testSliceMapSetIfAbsent(): void
    {
        run(function (): void {
            go(function (): void {
                $map = new SliceMap();
                $cm  = $map->getBucket('a');
                $this->assertTrue($cm->setIfAbsent('a', 'b'));
                $this->assertFalse($cm->setIfAbsent('a', 'b'));
            });
        });
    }
}
