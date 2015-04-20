<?php
namespace Ludo\Task;
use Ludo\Config\Config;

class TaskQueue {

    private $_tasks = array();
    private $_client;
    private $_errmsg;

    /**
     * @param string $queueName 队列名称
     */
    public function __construct($queueName) {
        $this->_tasks['name'] = $queueName;
        $this->_tasks['queue'] = array();
        $config = Config::server();
        $this->_client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_SYNC);
        $this->_client->connect($config['server.task_queue.host'], $config['server.task_queue.port']);
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

        $task = json_encode($this->_tasks);
        if ($this->_client->send($task)) {
            $this->_tasks = array();
        } else {
            $this->_errmsg = '发送数据失败';
            return false;
        }
        $this->_client->close();
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