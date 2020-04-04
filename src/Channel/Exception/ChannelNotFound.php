<?php

declare(strict_types=1);

namespace Notifier\Channel\Exception;

use LogicException;

final class ChannelNotFound extends LogicException implements NotifierChannelException
{
    public static function byName(string $channelName): self
    {
        return new self("Channel '$channelName' was not found");
    }
}
