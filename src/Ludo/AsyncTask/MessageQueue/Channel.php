<?php

namespace Ludo\AsyncTask\MessageQueue;

use RuntimeException;


/**
 * Message queue channel
 *
 * @package Ludo\AsyncTask
 */
class Channel
{
    /**
     * @var string $waiting waiting channel
     */
    protected string $waiting;

    /**
     * @var string $reserved reserved channel
     */
    protected string $reserved;

    /**
     * @var string $timeout timeout channel
     */
    protected string $timeout;

    /**
     * @var string $delayed delayed channel
     */
    protected string $delayed;

    /**
     * @var string $failed failed channel
     */
    protected string $failed;

    /**
     * Channel constructor.
     *
     * @param string $channel prefix of channel name
     */
    public function __construct(string $channel)
    {
        $this->waiting = $channel . '_waiting';
        $this->reserved = $channel . '_reserved';
        $this->delayed = $channel . '_delayed';
        $this->failed = $channel . '_failed';
        $this->timeout = $channel . '_timeout';
    }

    /**
     * Get channel by name
     *
     * @param string $channel channel name
     * @return mixed
     */
    public function get(string $channel): string
    {
        if (isset($this->{$channel}) && is_string($this->{$channel})) {
            return $this->{$channel};
        }

        throw new RuntimeException(sprintf('Channel %s is not exist.', $channel));
    }

    /**
     * Get waiting channel
     *
     * @return string
     */
    public function getWaiting(): string
    {
        return $this->waiting;
    }

    /**
     * Get reserved channel
     *
     * @return string
     */
    public function getReserved(): string
    {
        return $this->reserved;
    }

    /**
     * Get delayed channel
     *
     * @return string
     */
    public function getDelayed(): string
    {
        return $this->delayed;
    }

    /**
     * Get failed channel
     *
     * @return string
     */
    public function getFailed(): string
    {
        return $this->failed;
    }

    /**
     * Get timeout channel
     *
     * @return string
     */
    public function getTimeout(): string
    {
        return $this->timeout;
    }

}