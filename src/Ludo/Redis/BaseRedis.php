<?php

namespace Ludo\Redis;

use Redis;
use Closure;

class BaseRedis extends Redis
{
    public function __construct($config)
    {
        parent::__construct();
        $timeout = $config['timeout'] ?? 5;
        $this->connect($config['host'], $config['port'], $timeout);

        if (!empty($config['password'])) {
            $this->auth($config['password']);
        }

        if (!empty($config['db'])) {
            $this->select($config['db']);
        }
    }

    protected function getSafelyExpireSeconds()
    {
        return mt_rand(0, 86400);
    }

    /**
     * 随机延长过期时间，防止同一时间有大量key过期
     *
     * @param $key
     * @param $seconds
     */
    public function randExpire($key, $seconds)
    {
        $this->expire($key, $seconds + $this->getSafelyExpireSeconds());
    }

    /**
     * 随机延长过期时间，防止同一时间有大量key过期
     *
     * @param $key
     * @param $timestamp
     */
    public function randExpireAt($key, $timestamp)
    {
        $this->expireAt($key, $timestamp + $this->getSafelyExpireSeconds());
    }
}