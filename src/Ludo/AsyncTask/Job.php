<?php

namespace Ludo\AsyncTask;


/**
 * Message queue Job
 *
 * @package Ludo\AsyncTask
 */
abstract class Job implements JobInterface
{
    /**
     * @var int max handle times
     */
    protected int $maxHandleTimes = 0;

    /**
     * Get current job max handle times
     *
     * @return int
     */
    public function getMaxHandleTimes(): int
    {
        return $this->maxHandleTimes;
    }
}