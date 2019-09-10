<?php

namespace Ludo\AsyncTask\MessageQueue;

use Ludo\AsyncTask\JobInterface;
use Ludo\AsyncTask\Message;
use Ludo\AsyncTask\MessageInterface;
use Redis;
use RuntimeException;

class RedisMessageQueue extends MessageQueue
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var int $timeout max polling timeout
     */
    protected $timeout;

    /**
     * @var int $retryDelaySeconds Retry job delay seconds
     */
    protected $retryDelaySeconds;

    /**
     * @var int $handleTimeout Handle job timeout
     */
    protected $handleTimeout;

    /**
     * @var Channel $channel Channel object
     */
    protected $channel;

    /**
     * RedisMessageQueue constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $channel = $config['channel_prefix'] ?? 'queue';

        $this->redis = new Redis();
        $this->redis->connect($config['host'], $config['port']);
        $this->timeout = $config['timeout'] ?? 10;
        $this->retryDelaySeconds = $config['retry_seconds'] ?? 10;
        $this->handleTimeout = $config['handle_timeout'] ?? 10;

        $this->channel = new Channel($channel);
    }

    /**
     * Push job into message queue
     *
     * @param JobInterface $job job need to be executed
     * @param int $delay job to execute delay seconds, 0 if doesn't need delay
     * @return bool
     */
    public function push(JobInterface $job, int $delay = 0): bool
    {
        $message = new Message($job);
        $data = serialize($message);

        if ($delay == 0) {
            return boolval($this->redis->lPush($this->channel->getWaiting(), $data));
        } else {
            return boolval($this->redis->zAdd($this->channel->getDelayed(), [], time() + $delay, $data));
        }
    }

    /**
     * Delete a job from delayed queue
     *
     * @param JobInterface $job
     * @return bool
     */
    public function delete(JobInterface $job): bool
    {
        $message = new Message($job);
        $data = serialize($message);

        return boolval($this->redis->zRem($this->channel->getDelayed(), $data));
    }

    /**
     * Pop job from message queue
     *
     * @return array
     */
    public function pop(): array
    {
        $this->move($this->channel->getDelayed(), $this->channel->getWaiting());
        $this->move($this->channel->getReserved(), $this->channel->getTimeout());

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

        return [$result[1], $message];
    }

    /**
     * Ack message when handle successful
     *
     * @param string $data
     * @return bool
     */
    public function ack($data): bool
    {
        return $this->remove($data);
    }

    /**
     * Failed message
     *
     * @param string $data
     * @return bool
     */
    public function fail($data): bool
    {
        if ($this->remove($data)) {
            return boolval($this->redis->lPush($this->channel->getFailed(), $data));
        }

        return false;
    }

    /**
     * Retry execute failed message
     *
     * @param MessageInterface $message
     * @return bool
     */
    public function retry(MessageInterface $message): bool
    {
        $data = serialize($message);
        return $this->redis->zAdd($this->channel->getDelayed(), [], time() + $this->retryDelaySeconds, $data) > 0;
    }

    /**
     * Reload failed message into waiting queue
     *
     * @param string|null $channel
     * @return int
     */
    public function reload(string $channel = null): int
    {
        $channelName = $this->channel->getFailed();
        if ($channel) {
            if (!in_array($channel, ['timeout', 'waiting'])) {
                throw new RuntimeException(sprintf('Channel %s is not supported', $channel));
            }

            $channelName = $this->channel->get($channel);
        }

        $num = 0;
        while ($this->redis->rpoplpush($channelName, $this->channel->getWaiting())) {
            ++$num;
        }
        return $num;
    }

    /**
     * Flush message queue
     *
     * @param string|null $channel
     * @return bool
     */
    public function flush(string $channel = null): bool
    {
        $channelName = $this->channel->getFailed();
        if ($channel) {
            $channelName = $this->channel->get($channel);
        }

        return boolval($this->redis->del($channelName));
    }

    /**
     * Remove message from reserved channel
     *
     * @param string $data
     * @return bool
     */
    protected function remove($data): bool
    {
        return $this->redis->zRem($this->channel->getReserved(), $data) > 0;
    }

    /**
     * Move message from channel to channel
     *
     * @param string $from
     * @param string $to
     */
    protected function move(string $from, string $to): void
    {
        $now = time();

        if ($expiredData = $this->redis->zRevRangeByScore($from, $now, '-inf', ['LIMIT' => [0, 99]])) {
            foreach ($expiredData as $datum) {
                if ($this->redis->zRem($from, $datum) > 0) {
                    $this->redis->lPush($to, $datum);
                }
            }
        }
    }
}