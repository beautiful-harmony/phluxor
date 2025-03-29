<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Phluxor\ActorSystem;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class StdoutLogger implements LoggerInterface
{
    public function __invoke(ActorSystem $actorSystem): PsrLoggerInterface
    {
        $log = new Logger('Phluxor');
        $log->useLoggingLoopDetection(false);
        $log->pushHandler(new StreamHandler('php://stdout', Level::Info));

        return $log;
    }
}
