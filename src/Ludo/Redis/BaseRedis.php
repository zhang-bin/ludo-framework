<?php

namespace Ludo\Redis;

use Redis;
use RedisException;


/**
 * BaseRedis
 *
 * @package Ludo\Redis
 */
class BaseRedis extends Redis
{
    /**
     * BaseRedis constructor.
     *
     * @param array $config redis config
     * @throws RedisException
     */
    public function __construct(array $config)
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

    /**
     * Random expire seconds
     *
     * @return int
     */
    protected function getSafelyExpireSeconds(): int
    {
        return mt_rand(0, 86400);
    }

    /**
     * 随机延长过期时间，防止同一时间有大量key过期
     *
     * @param string $key redis key
     * @param int $seconds expire seconds
     * @throws RedisException
     */
    public function randExpire(string $key, int $seconds)
    {
        $this->expire($key, $seconds + $this->getSafelyExpireSeconds());
    }

    /**
     * 随机延长过期时间，防止同一时间有大量key过期
     *
     * @param string $key redis key
     * @param int $timestamp expire timestamp
     * @throws RedisException
     */
    public function randExpireAt(string $key, int $timestamp)
    {
        $this->expireAt($key, $timestamp + $this->getSafelyExpireSeconds());
    }
}