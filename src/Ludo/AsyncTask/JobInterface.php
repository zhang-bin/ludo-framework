<?php

namespace Ludo\AsyncTask;

interface JobInterface
{
    /**
     * Handle the job.
     */
    public function handle();

    /**
     * Get current job max handle times
     *
     * @return int
     */
    public function getMaxHandleTimes(): int;
}
