<?php
namespace Ludo\Task;

use Ludo\Config\Config;

class TaskQueue
{

    private $tasks = array();
    private $client;
    private $errmsg;
    /**
     * @var \Redis
     */
    private $queue;

    /**
     * @param string $queueName 队列名称
     */
    public function __construct($queueName)
    {
        $this->tasks['name'] = $queueName;
        $this->tasks['queue'] = array();
        $config = Config::get('server.task_queue');
        $this->client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_SYNC);
        $this->client->connect($config['host'], $config['port']);

        $this->queue = new \Redis();
        $this->queue->connect(Config::get('database.connections.redis.host'), Config::get('database.connections.redis.port'));
        $this->queue->select(1);
    }

    /**
     * @param string $url 执行任务的url地址
     * @param array $data 参数
     */
    public function addTask($url, $data = array())
    {
        $item = array();
        $item['url'] = $url;
        $item['data'] = $data;
        $this->tasks['queue'][] = $item;
    }

    /**
     * @return bool
     */
    public function push()
    {
        if (empty($this->tasks['queue'])) {
            $this->errmsg = '队列不存在';
            return false;
        }
        if (!$this->client->isConnected()) {
            $this->errmsg = '服务器连接失败';
            return false;
        }

        foreach ($this->tasks['queue'] as $task) {
            if (empty($task)) continue;
            $this->queue->lPush($this->tasks['name'], json_encode($task));
        }
        if ($this->client->send($this->tasks['name'])) {
            $this->tasks = array();
        } else {
            $this->errmsg = '发送数据失败';
            return false;
        }
        $this->client->close();
        return true;
    }

    /**
     * 返回队列剩余长度
     *
     * @return int
     */
    public function curLen()
    {
        return $this->queue->lLen($this->tasks['name']);
    }

    /**
     * 取得错误信息
     *
     * @return string
     */
    public function errmsg()
    {
        return $this->errmsg;
    }
}
