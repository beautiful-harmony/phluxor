<?php

declare(strict_types=1);

namespace Test\Persistence\PgSql;

use PDO;
use Phluxor\Persistence\PgSql\Connection;
use Phluxor\Persistence\PgSql\Dsn;
use PHPUnit\Framework\TestCase;

use function assert;
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

class PgSqlConnectionTest extends TestCase
{
    public function testConnection(): void
    {
        run(function (): void {
            go(function (): void {
                $pool = new Connection(
                    new Dsn(
                        '127.0.0.1',
                        5432,
                        'sample',
                        'postgres',
                        'postgres',
                    ),
                );
                $conn = $pool->proxy();
                assert($conn instanceof PDO);
                $st = $conn->query('SELECT NOW()');
                $this->assertNotFalse($st->fetchAll());
            });
        });
    }
}
