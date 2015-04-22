<?php
namespace Ludo\Counter;

use Ludo\Config\Config;

class Counter
{
    /**
     * @var \Redis
     */
    private $db;

    private $counters = array();

    const PREFIX = 'counter_';

    public function __construct()
    {
        $this->db = new \Redis();
        $this->db->connect(Config::get('database.connections.redis.host'), Config::get('database.connections.redis.port'));
        $this->db->select(4);
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
        if (isset($this->counters[$name])) return false;
        $this->counters[$name] = $name;

        $key = self::PREFIX.$name;
        $this->db->incrBy($key, $value);
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
        if (!isset($this->counters[$name])) return false;
        unset($this->counters[$name]);

        $key = self::PREFIX.$name;
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
        return isset($this->counters[$name]);
    }

    /**
     * get the counter value
     *
     * @param string $name counter name
     * @return bool|string counter value when success, otherwise return false
     */
    public function get($name)
    {
        if (!isset($this->counters[$name])) return false;

        $key = self::PREFIX.$name;
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
        if (!isset($this->counters[$name])) return false;

        $key = self::PREFIX.$name;
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
        if (!isset($this->counters[$name])) return false;

        $key = self::PREFIX.$name;
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
        if (!isset($this->counters[$name])) return false;

        $key = self::PREFIX.$name;
        return $this->db->decrBy($key, $value);
    }
}
