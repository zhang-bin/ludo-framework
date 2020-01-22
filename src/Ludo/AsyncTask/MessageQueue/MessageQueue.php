<?php

namespace Ludo\AsyncTask\MessageQueue;

use Ludo\AsyncTask\Message;
use Ludo\AsyncTask\MessageInterface;
use Throwable;

/**
 * Message Queue to consume job
 *
 * @package Ludo\AsyncTask\MessageQueue
 */
abstract class MessageQueue implements MessageQueueInterface
{
    /**
     * Consume job and execute it
     */
    public function consume(): void
    {
        while (true) {
            [$data, $message] = $this->pop();

            if (false === $data) {
                continue;
            }

            parallel([function () use ($message, $data) {
                try {
                    if ($message instanceof Message) {
                        $message->handleTimes++;
                        $message->job()->handle();
                    }

                    $this->ack($data);
                } catch (Throwable $e) {
                    if ($message->shouldHandleAgain() && $this->remove($data)) {
                        $this->retry($message);
                    } else {
                        $this->fail($data);
                    }
                }
            }]);
        }
    }

    /**
     * Handle a job again
     *
     * @param MessageInterface $message message object
     * @return bool
     */
    abstract protected function retry(MessageInterface $message): bool;

    /**
     * Remove job from reserved queue
     *
     * @param string $data stringify job
     * @return bool
     */
    abstract protected function remove(string $data): bool;
}