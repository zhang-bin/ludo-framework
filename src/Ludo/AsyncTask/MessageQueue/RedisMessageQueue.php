<?php

namespace Ludo\AsyncTask\MessageQueue;

use Ludo\AsyncTask\JobInterface;
use Ludo\AsyncTask\Message;
use Ludo\AsyncTask\MessageInterface;
use Ludo\Exception\AsyncTaskException;
use Ludo\Redis\BaseRedis;
use RuntimeException;
use Ludo\Support\ServiceProvider;
use RedisException;


/**
 * Message Queue use redis
 *
 * @package Ludo\AsyncTask\MessageQueue
 */
class RedisMessageQueue extends MessageQueue
{
    /**
     * @var BaseRedis $redis
     */
    protected BaseRedis $redis;

    /**
     * @var int $timeout max polling timeout
     */
    protected int $timeout;

    /**
     * @var int $retryDelaySeconds Retry job delay seconds
     */
    protected int $retryDelaySeconds;

    /**
     * @var int $handleTimeout Handle job timeout
     */
    protected int $handleTimeout;

    /**
     * @var Channel $channel Channel object
     */
    protected Channel $channel;

    /**
     * RedisMessageQueue constructor.
     *
     * @param array $config message queue config
     */
    public function __construct(array $config)
    {
        $channel = $config['channel_prefix'] ?? 'queue';

        $this->redis = ServiceProvider::getInstance()->getRedisHandler($config['redis']);
        $this->timeout = $config['timeout'] ?? 10;
        $this->retryDelaySeconds = $config['retry_seconds'] ?? 10;
        $this->handleTimeout = $config['handle_timeout'] ?? 10;

        $this->channel = new Channel($channel);
    }

    /**
     * Push job into message queue
     *
     * @param JobInterface $job job need to be executed
     * @param int $delay job to execute delay seconds, 0 if it doesn't need delay
     * @return bool
     * @throws AsyncTaskException
     */
    public function push(JobInterface $job, int $delay = 0): bool
    {
        $message = new Message($job);
        $data = serialize($message);

        try {
            if ($delay == 0) {
                return boolval($this->redis->lPush($this->channel->getWaiting(), $data));
            } else {
                return boolval($this->redis->zAdd($this->channel->getDelayed(), [], time() + $delay, $data));
            }
        } catch (RedisException $e) {
            throw new AsyncTaskException('push job failed', 1, $e);
        }
    }

    /**
     * Delete a job from delayed queue
     *
     * @param JobInterface $job job need to be executed
     * @return bool
     * @throws AsyncTaskException
     */
    public function delete(JobInterface $job): bool
    {
        $message = new Message($job);
        $data = serialize($message);

        try {
            return boolval($this->redis->zRem($this->channel->getDelayed(), $data));
        } catch (RedisException $e) {
            throw new AsyncTaskException('delete job failed', 2, $e);
        }
    }

    /**
     * Pop job from message queue
     *
     * @return array
     * @throws AsyncTaskException
     */
    public function pop(): array
    {
        $this->move($this->channel->getDelayed(), $this->channel->getWaiting());
        $this->move($this->channel->getReserved(), $this->channel->getTimeout());

        try {
            $result = $this->redis->brPop($this->channel->getWaiting(), $this->timeout);
            if (empty($result)) {
                return [false, null];
            }

            $data = $result[1];
            $message = unserialize($data);
            if (empty($message)) {
                return [false, null];
            }

            $this->redis->zAdd($this->channel->getReserved(), [], time() + $this->handleTimeout, $data);
        } catch (RedisException $e) {
            throw new AsyncTaskException('pop job failed', 3, $e);
        }

        return [$result[1], $message];
    }

    /**
     * Ack message when handle successful
     *
     * @param string $data message
     * @return bool
     * @throws AsyncTaskException
     */
    public function ack(string $data): bool
    {
        return $this->remove($data);
    }

    /**
     * Failed message
     *
     * @param string $data message
     * @return bool
     * @throws AsyncTaskException
     */
    public function fail(string $data): bool
    {
        if ($this->remove($data)) {
            try {
                return boolval($this->redis->lPush($this->channel->getFailed(), $data));
            } catch (RedisException $e) {
                throw new AsyncTaskException('handle failed job', 4, $e);
            }
        }

        return false;
    }

    /**
     * Retry execute failed message
     *
     * @param MessageInterface $message message object
     * @return bool
     * @throws AsyncTaskException
     */
    public function retry(MessageInterface $message): bool
    {
        $data = serialize($message);
        try {
            return $this->redis->zAdd($this->channel->getDelayed(), [], time() + $this->retryDelaySeconds, $data) > 0;
        } catch (RedisException $e) {
            throw new AsyncTaskException('handle job retry', 5, $e);
        }
    }

    /**
     * Reload failed message into waiting queue
     *
     * @param ?string $channel channel name
     * @return int
     * @throws AsyncTaskException
     */
    public function reload(?string $channel = null): int
    {
        $channelName = $this->channel->getFailed();
        if ($channel) {
            if (!in_array($channel, ['timeout', 'waiting'])) {
                throw new RuntimeException(sprintf('Channel %s is not supported', $channel));
            }

            $channelName = $this->channel->get($channel);
        }

        $num = 0;
        try {
            while ($this->redis->rpoplpush($channelName, $this->channel->getWaiting())) {
                ++$num;
            }
        } catch (RedisException $e) {
            throw new AsyncTaskException('reload failed message failed', 6, $e);
        }
        return $num;
    }

    /**
     * Flush message queue
     *
     * @param ?string $channel failed channel name
     * @return bool
     * @throws AsyncTaskException
     */
    public function flush(?string $channel = null): bool
    {
        $channelName = $this->channel->getFailed();
        if ($channel) {
            $channelName = $this->channel->get($channel);
        }

        try {
            return boolval($this->redis->del($channelName));
        } catch (RedisException $e) {
            throw new AsyncTaskException('flush message failed', 7, $e);
        }
    }

    /**
     * Remove message from reserved channel
     *
     * @param string $data message
     * @return bool
     * @throws AsyncTaskException
     */
    protected function remove(string $data): bool
    {
        try {
            return $this->redis->zRem($this->channel->getReserved(), $data) > 0;
        } catch (RedisException $e) {
            throw new AsyncTaskException('remove message failed', 8, $e);
        }
    }

    /**
     * Move message from channel to channel
     *
     * @param string $from original channel name
     * @param string $to target channel name
     * @throws AsyncTaskException
     */
    protected function move(string $from, string $to): void
    {
        $now = time();

        try {
            if ($expiredData = $this->redis->zRevRangeByScore($from, $now, '-inf', ['LIMIT' => [0, 99]])) {
                foreach ($expiredData as $datum) {
                    if ($this->redis->zRem($from, $datum) > 0) {
                        $this->redis->lPush($to, $datum);
                    }
                }
            }
        } catch (RedisException $e) {
            throw new AsyncTaskException('move message failed', 9, $e);
        }
    }
}