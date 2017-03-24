<?php
namespace Ludo\Redis;

use Redis;
use Closure;

class BaseRedis extends Redis
{
    /**
     * 支持double-checked locking的获取数据，并且支持空数据锁
     *
     * @param string $key 键值
     * @param array $expire 过期值 e.g. expire_at => timestamp || expire => seconds
     * @param Closure $closure 重新获取数据的匿名函数
     * @return bool|mixed|string
     */
    public function getSafely($key, array $expire = array(), Closure $closure)
    {
        $nullMutexKey = $this->getNullMutexKey($key);
        if ($this->exists($nullMutexKey)) {//如果有空数据锁，那么直接返回
            return null;
        }

        $value = $this->get($key);
        if (empty($value)) {
            $mutexKey = $this->getMutexKey($key);
            if ($this->set($mutexKey, 1, array('nx', 'ex' => 300))) {//设置锁成功
                $value = $closure();
                if (empty($value)) {
                    $this->set($nullMutexKey, 1, array('nx', 'ex' => 600));//设置锁
                } else {
                    $this->setSafely($key, $expire);
                }
                $this->del($mutexKey);
            } else {
                usleep(20000);//20毫秒
                $value = $this->get($key);
            }
        }
        return $value;
    }

    /**
     * 缓存数据，同时删除空数据锁
     *
     * @param string $key 键值
     * @param mixed $value 数据
     * @param array $expire 过期值 e.g. expire_at => timestamp || expire => seconds
     */
    public function setSafely($key, $value, array $expire = array())
    {
        $expireKey = key($expire);
        switch ($expireKey) {
            case EXPIRE:
                $this->set($key, $value, $expire[$expireKey]);
                break;
            case EXPIRE_AT:
                $this->set($key, $value);
                $this->expireAt($key, $expire[$expireKey]);
                break;
            default:
                $this->set($key, $value);
                break;
        }

        $nullMutexKey = $this->getNullMutexKey($key);
        $this->del($nullMutexKey);
    }

    /**
     * 支持double-checked locking的获取数据，并且支持空数据锁
     *
     * @param string $key 键值
     * @param null $hashKey hash键值,null表示获取全部数据
     * @param array $expire 过期值 e.g. expire_at => timestamp || expire => seconds
     * @param Closure $getAll 获取所有数据
     * @param Closure $getOne 获取单条数据
     * @return array|mixed|null|string
     */
    public function hGetSafely($key, $hashKey = null, array $expire = array(), Closure $getAll, Closure $getOne)
    {
        if (!$this->exists($key)) {
            $mutexKey = $this->getMutexKey($key);
            if ($this->set($mutexKey, 1, array('nx', 'ex' => 300))) {//设置锁成功
                $values = $getAll();
                foreach ($values as $valueHashKey => $value) {
                    $this->hSet($key, $valueHashKey, $value);
                }
                $expireKey = key($expire);
                switch ($expireKey) {
                    case EXPIRE:
                        $this->expire($key, $expire[$expireKey]);
                        break;
                    case EXPIRE_AT:
                        $this->expireAt($key, $expire[$expireKey]);
                        break;
                    default:
                        break;
                }
                $this->del($mutexKey);
            } else {
                usleep(20000);//20毫秒
            }
        }

        if (is_null($hashKey)) {
            $data = $this->hGetAll($key);
        } else {
            if (!$this->hExists($key, $hashKey)) {
                $nullMutexKey = $this->getNullMutexKey($key.'_'.$hashKey);
                if ($this->exists($nullMutexKey)) {
                    $data = null;
                } else {
                    $value = $getOne();
                    if (empty($value)) {
                        $this->set($nullMutexKey, 1, array('nx', 'ex' => 600));//设置空数据锁
                        $data = null;
                    } else {
                        $this->hSet($key, $hashKey, $value);
                        $data = $value;
                    }
                }
            } else {
                $data = $this->hGet($key, $hashKey);
            }
        }
        return $data;
    }

    /**
     * 缓存数据
     *
     * @param string $key 键值
     * @param string $hashKey hash键值
     * @param mixed $value 数据
     * @param array $expire 过期值 e.g. expire_at => timestamp || expire => seconds
     */
    public function hSetSafely($key, $hashKey, $value, array $expire = array()) {
        $this->hSet($key, $hashKey, $value);
        $expireKey = key($expire);
        switch ($expireKey) {
            case EXPIRE:
                $this->expire($key, $expire[$expireKey]);
                break;
            case EXPIRE_AT:
                $this->expireAt($key, $expire[$expireKey]);
                break;
            default:
                break;
        }

        $nullMutexKey = $this->getNullMutexKey($key.'_'.$hashKey);
        $this->del($nullMutexKey);
    }

    /**
     * 数据锁
     *
     * @param $key
     * @return string
     */
    private function getMutexKey($key) {
        return 'mutex_'.$key;
    }

    /**
     * 空数据锁
     *
     * @param $key
     * @return string
     */
    private function getNullMutexKey($key) {
        return 'null_mutex_'.$key;
    }
}