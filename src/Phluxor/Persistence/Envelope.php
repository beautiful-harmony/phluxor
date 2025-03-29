<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use Exception;
use Google\Protobuf\Internal\Message;
use Phluxor\Persistence\Exception\UnknownMessageException;
use ReflectionClass;
use ReflectionException;

use function htmlspecialchars_decode;
use function json_decode;
use function sprintf;

readonly class Envelope
{
    public function __construct(
        private string $message,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function message(): Message
    {
        $json    = htmlspecialchars_decode($this->message);
        $decoded = json_decode($json, true);
        $ref     = new ReflectionClass($decoded['typeName']);
        $obj     = $ref->newInstance();
        if (! $obj instanceof Message) {
            throw new UnknownMessageException(
                sprintf('Unknown message type: %s', $decoded['typeName']),
            );
        }

        $obj->mergeFromJsonString($decoded['rawMessage']);

        return $obj;
    }
}
