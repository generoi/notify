<?php

declare(strict_types=1);

namespace Notify;

use Countable;
use IteratorAggregate;
use ArrayIterator;
use JsonSerializable;
use Notify\Message\Actor\ActorInterface;

class Recipients implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @var ActorInterface[]
     */
    private $recipients;

    public function __construct(array $recipients)
    {
        $this->recipients = $recipients;
    }

    public function count()
    {
        return count($this->recipients);
    }

    public function isEmpty()
    {
        return (0 === $this->count());
    }

    public function getIterator()
    {
        return new ArrayIterator($this->recipients);
    }

    public function toArray()
    {
        return $this->recipients;
    }

    public function jsonSerialize()
    {
        return array_map('strval', $this->toArray());
    }
}
