<?php

namespace Ludo\AsyncTask;

/**
 * Message Interface
 *
 * @package Ludo\AsyncTask
 */
interface MessageInterface
{
    /**
     * Get current job object
     *
     * @return JobInterface
     */
    public function job(): JobInterface;

    /**
     * Whether the queue should handle job again
     *
     * @return bool
     */
    public function shouldHandleAgain(): bool;
}