<?php

namespace Ludo\AsyncTask\MessageQueue;

use Ludo\AsyncTask\MessageInterface;
use Ludo\Support\Facades\Config;
use RuntimeException;


/**
 * Message Queue Factory
 *
 * @package Ludo\AsyncTask\MessageQueue
 */
class MessageQueueFactory
{
    /**
     * @var MessageQueueInterface|MessageInterface
     */
    protected MessageQueueInterface|MessageInterface $messageQueue;

    /**
     * MessageQueueFactory constructor.
     */
    public function __construct()
    {
        $config = Config::get('async_queue');

        $messageQueueClass = $config['message_queue'];
        if (!class_exists($messageQueueClass)) {
            throw new RuntimeException(sprintf('[Error] class %s not found', $messageQueueClass));
        }

        $messageQueue = new $messageQueueClass($config);
        if (!$messageQueue instanceof MessageQueueInterface) {
            throw new RuntimeException(sprintf('[Error] class %s is not instanceof %s', $messageQueueClass, MessageQueueInterface::class));
        }

        $this->messageQueue = $messageQueue;
    }

    /**
     * Get current message queue object
     *
     * @return MessageQueueInterface
     */
    public function get(): MessageQueueInterface
    {
        return $this->messageQueue;
    }
}