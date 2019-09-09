<?php

namespace Ludo\AsyncTask;

abstract class Job implements JobInterface
{
    /**
     * @var int max handle times
     */
    protected $maxHandleTimes = 0;

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