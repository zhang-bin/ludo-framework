<?php
namespace Ludo\Counter;

use Ludo\Config\Config;

class Counter
{
    /**
     * @var \Redis
     */
    private $db;

    const PREFIX = 'counter_';

    public function __construct()
    {
        $this->db = new \Redis();
        $this->db->connect(Config::get('database.connections.redis.host'), Config::get('database.connections.redis.port'));
        $this->db->select(1);
    }

    /**
     * setup a counter
     *
     * @param string $name counter name
     * @param int $value initial value, default is 0
     * @return bool true if setup success, false if counter exist
     */
    public function create($name, $value = 0)
    {
        $key = self::PREFIX.$name;
        if ($this->db->exists($key)) return false;
        $this->db->incrBy($key, $value);
        return true;
    }

    /**
     * Sets an expiration date (a timeout) on an counter.
     *
     * @param string $name counter name
     * @param int $ttl remaining time to Live, in seconds.
     * @return bool TRUE in case of success, FALSE in case of failure.
     */
    public function expire($name, $ttl) {
        $key = self::PREFIX.$name;
        if (!$this->db->exists($key)) return false;
        $this->db->expire($key, $ttl);
        return true;
    }

    /**
     * Sets an expiration date (a timestamp) on an counter.
     *
     * @param string $name counter name
     * @param int $timestamp Unix timestamp. The counter's date of death, in seconds from Epoch time.
     * @return bool TRUE in case of success, FALSE in case of failure.
     */
    public function expireAt($name, $timestamp) {
        $key = self::PREFIX.$name;
        if (!$this->db->exists($key)) return false;
        $this->db->expireAt($key, $timestamp);
        return true;
    }

    /**
     * delete a counter
     *
     * @param string $name counter name
     * @return bool true if delete success, false if counter not exist
     */
    public function remove($name)
    {
        $key = self::PREFIX.$name;
        if (!$this->db->exists($key)) return false;
        $this->db->del($key);
        return true;
    }

    /**
     * verify if the specified counter exists.
     *
     * @param string $name counter name
     * @return bool if counter exist, return true, otherwise return false
     */
    public function exists($name)
    {
        $key = self::PREFIX.$name;
        return $this->db->exists($key);
    }

    /**
     * get the counter value
     *
     * @param string $name counter name
     * @return bool|string counter value when success, otherwise return false
     */
    public function get($name)
    {
        $key = self::PREFIX.$name;
        if (!$this->db->exists($key)) return false;
        return $this->db->get($key);
    }

    /**
     * set counter value
     *
     * @param string $name counter name
     * @param int $value
     * @return bool true if set success, otherwise false
     */
    public function set($name, $value)
    {
        $key = self::PREFIX.$name;
        if (!$this->db->exists($key)) return false;
        $this->db->set($key, $value);
        return true;
    }

    /**
     * increment counter
     *
     * @param string $name counter name
     * @param int $value
     * @return bool|int the new value
     */
    public function incr($name, $value = 1)
    {
        $key = self::PREFIX.$name;
        if (!$this->db->exists($key)) return false;
        return $this->db->incrBy($key, $value);
    }

    /**
     * decrement counter
     *
     * @param string $name counter name
     * @param int $value
     * @return bool|int the new value
     */
    public function decr($name, $value = 1)
    {
        $key = self::PREFIX.$name;
        if (!$this->db->exists($key)) return false;
        return $this->db->decrBy($key, $value);
    }
}
