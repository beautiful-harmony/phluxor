<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

interface ReceiverInterface extends
    InfoPartInterface,
    ReceiverPartInterface,
    SenderPartInterface,
    MessagePartInterface
{
}
