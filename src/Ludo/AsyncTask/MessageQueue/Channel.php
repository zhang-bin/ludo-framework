<?php

namespace Ludo\AsyncTask\MessageQueue;

use RuntimeException;

/**
 * Class Channel
 *
 * @package Ludo\AsyncTask
 */
class Channel
{
    /**
     * @var string $waiting waiting channel
     */
    protected $waiting;

    /**
     * @var string $reserved reserved channel
     */
    protected $reserved;

    /**
     * @var string $timeout timeout channel
     */
    protected $timeout;

    /**
     * @var string $delayed delayed channel
     */
    protected $delayed;

    /**
     * @var string $failed failed channel
     */
    protected $failed;

    public function __construct(string $channel)
    {
        $this->waiting = $channel.'_waiting';
        $this->reserved = $channel.'_reserved';
        $this->delayed = $channel.'_delayed';
        $this->failed = $channel.'_failed';
        $this->timeout = $channel.'_timeout';
    }

    /**
     * Get channel by name
     *
     * @param string $channel
     * @return mixed
     */
    public function get(string $channel)
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