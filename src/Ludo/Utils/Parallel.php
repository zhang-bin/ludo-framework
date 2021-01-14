<?php

namespace Ludo\Utils;

use Swoole\Coroutine\Channel;
use Swoole\Coroutine;


/**
 * Parallel execute tasks using swoole coroutine
 *
 * @package Ludo\Utils
 */
class Parallel
{
    /**
     * @var array $callbacks callback
     */
    private array $callbacks = [];

    /**
     * Register task
     *
     * @param callable $callback callback
     * @param ?string $key callback name
     */
    public function add(callable $callback, string $key = null): void
    {
        if (is_null($key)) {
            $this->callbacks[] = $callback;
        } else {
            $this->callbacks[$key] = $callback;
        }
    }

    /**
     * Wait all task execute done
     *
     * @return array
     */
    public function wait(): array
    {
        $result = [];
        $callbackNum = count($this->callbacks);
        $channel = new Channel($callbackNum);
        foreach ($this->callbacks as $key => $callback) {
            Coroutine::create(function () use ($callback, $key, $channel, &$result) {
                $result[$key] = call_user_func($callback);
                $channel->push(true);
            });
        }

        Coroutine::create(function () use ($channel, $callbackNum) {
            while ($callbackNum--) {
                $channel->pop();
            }
        });

        return $result;
    }
}