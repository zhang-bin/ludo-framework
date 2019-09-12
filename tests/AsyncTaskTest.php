<?php
namespace LudoTest;

use Ludo\AsyncTask\Job;
use Ludo\AsyncTask\MessageInterface;
use Ludo\AsyncTask\MessageQueue\RedisMessageQueue;
use Ludo\Support\Facades\Config;
use PHPUnit\Framework\TestCase;
use Redis;

class AsyncTaskTest extends TestCase
{
    private $redis;
    private $context;

    public function setUp(): void
    {
        $this->redis = new Redis();
        $this->redis->connect(Config::get('async_queue.host'), Config::get('async_queue.port'));
    }

    public function testPush()
    {
        $waitingQueue = Config::get('async_queue.channel_prefix').'_waiting';
        $delayedQueue = Config::get('async_queue.channel_prefix').'_delayed';
        $this->redis->del($waitingQueue, $delayedQueue);

        $messageQueue = new RedisMessageQueue(Config::get('async_queue'));

        $job = $this->createMock(Job::class);
        $job->id = uniqid();
        $messageQueue->push($job);
        $message = $this->redis->lIndex($waitingQueue, 0);
        /** @var $message MessageInterface */
        $message = unserialize($message);
        $this->assertSame($job->id, $message->job()->id);
        $this->redis->del($waitingQueue);

        $job = $this->createMock(Job::class);
        $job->id = uniqid();
        $messageQueue->push($job, 5);
        $message = $this->redis->zRange($delayedQueue, 0, 0, true);
        $delayedSeconds = current($message) - time();
        /** @var $message MessageInterface */
        $message = unserialize(key($message));
        $this->assertSame($job->id, $message->job()->id);
        $this->assertContains($delayedSeconds, [5, 4]);
        $this->redis->del($delayedQueue);
    }
}