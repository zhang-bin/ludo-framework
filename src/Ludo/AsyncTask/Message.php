<?php

namespace Ludo\AsyncTask;

class Message implements MessageInterface
{
    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var int current handle times
     */
    public $handleTimes = 0;

    /**
     * Message constructor.
     *
     * @param JobInterface $job
     */
    public function __construct(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * Get current job object
     *
     * @return JobInterface
     */
    public function job(): JobInterface
    {
        return $this->job;
    }

    /**
     * Whether the queue should handle job again
     *
     * @return bool
     */
    public function shouldHandleAgain(): bool
    {
        return ($this->job->getMaxHandleTimes() > $this->handleTimes++);
    }
}