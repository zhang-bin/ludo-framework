<?php
namespace Ludo\Task;

use Ludo\Config\Config;
use Ludo\Support\ServiceProvider;

class TaskQueueServer
{
    private $config = array();
    /**
     * @var \swoole_server
     */
    private $server;

    /**
     * @var \Redis
     */
    private $queue;

    public function __construct()
    {
        swoole_set_process_name('php task_queue manager');
        $this->config = Config::get('server.task_queue');
        $this->queue = new \Redis();
    }

    public function run()
    {
        $this->server = new \swoole_server($this->config['bind'], $this->config['port']);
        $config = array(
            'worker_num' => $this->config['worker_num'],
            'log_file' => $this->config['log_file'],
            'task_worker_num' => $this->config['task_worker_num'],
            'user' => $this->config['user'],
            'group' => $this->config['group'],
            'daemonize' => 1,
            'dispatch_mode' => 2,
            'open_tcp_nodelay' => true,
        );
        $this->server->set($config);
        $this->server->on('Receive', array($this, 'receive'));
        $this->server->on('WorkerStart', array($this, 'workerStart'));
        $this->server->on('Start', array($this, 'start'));
        $this->server->on('Task', array($this, 'task'));
        $this->server->on('Finish', array($this, 'finish'));
        $this->server->start();
    }

    public function receive(\swoole_server $server, $fd, $fromId, $data)
    {
        switch ($data) {
            case 'reload':
                $server->reload();
                break;
            case 'stop':
                $server->shutdown();
                break;
            default:
                $this->queue->connect(Config::get('database.connections.redis.host'), Config::get('database.connections.redis.port'));
                $this->queue->select(1);
                do {
                    $task = json_decode($this->queue->rPop($data), true);
                    if (empty($task)) break;
                    $job = array('name' => $data, 'task' => $task);
                    $server->task(json_encode($job));
                } while(1);
                break;
        }
    }

    public function task(\swoole_server $server, $taskId, $fromId, $data)
    {
        $data = json_decode($data, true);
        $name = $data['name'];
        $data = $data['task'];
        $result = curlPost($data['url'], http_build_query($data['data']));
        if ($result === false) {
            $this->queue->lPush($name, json_encode($data));
        }
        $log = sprintf('Url: %s, Task Data: %s ---- Task Result: %s', $data['url'], json_encode($data['data'], JSON_UNESCAPED_UNICODE), $result);
        ServiceProvider::getInstance()->getLogHandler()->debug($log);
    }

    public function finish(\swoole_server $server, $taskId, $data)
    {

    }

    public function workerStart(\swoole_server $server, $workerId)
    {
        if($workerId >= $server->setting['worker_num']) {
            swoole_set_process_name('php task_queue task worker');
        } else {
            swoole_set_process_name('php task_queue event worker');
        }
    }

    public function start(\swoole_server $server)
    {
        swoole_set_process_name('php task_queue master');
    }
}
