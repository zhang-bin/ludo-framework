<?php
namespace Ludo\Task;
use Ludo\Config\Config;

class TaskQueue {

    private $_tasks = array();
    private $_client;
    private $_errmsg;
    /**
     * @var Redis
     */
    private $_queue;

    /**
     * @param string $queueName 队列名称
     */
    public function __construct($queueName) {
        $this->_tasks['name'] = $queueName;
        $this->_tasks['queue'] = array();
        $config = Config::get('server.task_queue');
        $this->_client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_SYNC);
        $this->_client->connect($config['host'], $config['port']);

        $this->_queue = new \Redis();
        $this->_queue->connect(Config::get('database.connections.redis.host'), Config::get('database.connections.redis.port'));
        $this->_queue->select(3);
    }

    /**
     * @param string $url 执行任务的url地址
     * @param array $data 参数
     */
    public function addTask($url, $data = array()) {
        $item = array();
        $item['url'] = $url;
        $item['data'] = $data;
        $this->_tasks['queue'][] = $item;
    }

    /**
     * @return bool
     */
    public function push() {
        if (empty($this->_tasks['queue'])) {
            $this->_errmsg = '队列不存在';
            return false;
        }
        if (!$this->_client->isConnected()) {
            $this->_errmsg = '服务器连接失败';
            return false;
        }

        foreach ($this->_tasks['queue'] as $task) {
            if (empty($task)) continue;
            $this->_queue->lPush($this->_tasks['name'], json_encode($task));
        }
        if ($this->_client->send($this->_tasks['name'])) {
            $this->_tasks = array();
        } else {
            $this->_errmsg = '发送数据失败';
            return false;
        }
        $this->_client->close();
    }

    /**
     * 返回队列剩余长度
     *
     * @return int
     */
    public function curLen() {
        return $this->_queue->lLen($this->_tasks['name']);
    }

    /**
     * 取得错误信息
     *
     * @return string
     */
    public function errmsg() {
        return $this->_errmsg;
    }
}