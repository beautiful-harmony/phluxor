<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

use JsonSerializable;
use ReflectionClass;

readonly class Message implements JsonSerializable
{
    public function __construct(
        private \Google\Protobuf\Internal\Message $message,
    ) {
    }

    private function toMessageEnvelope(): MessageEnvelope
    {
        $ref = new ReflectionClass($this->message);

        return new MessageEnvelope(
            $ref->getName(),
            $this->message->serializeToJsonString(),
        );
    }

    public function jsonSerialize(): array
    {
        $env = $this->toMessageEnvelope();

        return [
            'typeName' => $env->typeName,
            'rawMessage' => $env->rawMessage,
        ];
    }
}
