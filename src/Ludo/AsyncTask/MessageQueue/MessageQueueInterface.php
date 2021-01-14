<?php

namespace Ludo\AsyncTask\MessageQueue;

use Ludo\AsyncTask\JobInterface;


/**
 * Message Queue Interface
 *
 * @package Ludo\AsyncTask\MessageQueue
 */
interface MessageQueueInterface
{

    /**
     * Push a job to queue
     *
     * @param JobInterface $job job object
     * @param int $delay delay seconds
     * @return bool
     */
    public function push(JobInterface $job, int $delay = 0): bool;

    /**
     * Delete a delay job from queue
     *
     * @param JobInterface $job job object
     * @return bool
     */
    public function delete(JobInterface $job): bool;

    /**
     * Pop a job from queue
     *
     * @return array
     */
    public function pop(): array;

    /**
     * Ack a job
     *
     * @param string $data stringify job
     * @return bool
     */
    public function ack(string $data): bool;

    /**
     * Push a job to failed queue
     *
     * @param string $data stringify job
     * @return bool
     */
    public function fail(string $data): bool;

    /**
     * Consume jobs from queue
     */
    public function consume(): void;

    /**
     * Reload failed job into waiting queue
     *
     * @param ?string $channel channel name
     * @return int
     */
    public function reload(string $channel = null): int;

    /**
     * Flush all failed message from failed queue
     *
     * @param ?string $channel failed channel name
     * @return bool
     */
    public function flush(string $channel = null): bool;
}