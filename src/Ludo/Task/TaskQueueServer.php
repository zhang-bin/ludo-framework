<?php
namespace Ludo\Task;
use Ludo\Config\Config;
use Ludo\Support\ServiceProvider;

class TaskQueueServer {
    private $_config = array();
    private $_server;

    public function __construct() {
        swoole_set_process_name('php task_queue manager');
        $this->_config = Config::server();
    }

    public function run() {
        $this->_server = new \swoole_server($this->_config['server.task_queue.bind'], $this->_config['server.task_queue.port']);
        $config = array(
            'worker_num' => $this->_config['server.task_queue.worker_num'],
            'log_file' => $this->_config['server.task_queue.log_file'],
            'task_worker_num' => $this->_config['server.task_queue.task_worker_num'],
            'user' => $this->_config['server.task_queue.user'],
            'group' => $this->_config['server.task_queue.group'],
            'daemonize' => 1,
            'dispatch_mode' => 1,
        );
        $this->_server->set($config);
        $this->_server->on('Receive', array($this, 'receive'));
        $this->_server->on('WorkerStart', array($this, 'workerStart'));
        $this->_server->on('Start', array($this, 'start'));
        $this->_server->on('Task', array($this, 'task'));
        $this->_server->on('Finish', array($this, 'finish'));
        $this->_server->start();
    }

    public function receive(\swoole_server $server, $fd, $fromId, $data) {
        $data = json_decode($data, true);
        foreach ($data['queue'] as $item) {
            $server->task(json_encode($item));
        }
        $server->send($fd, 'ok');
    }

    public function task(\swoole_server $server, $taskId, $fromId, $data) {
        $data = json_decode($data, true);
        $result = curlPost($data['url'], http_build_query($data['data']));
        $log = sprintf('%s - Task Info: %s ---- Task Result: %s', date(TIME_FORMAT), json_encode($data), $result);
        ServiceProvider::getInstance()->getLogHandler()->debug($log);
    }

    public function finish(\swoole_server $server, $taskId, $data) {

    }

    public function workerStart(\swoole_server $server, $workerId) {
        if($workerId >= $server->setting['worker_num']) {
            swoole_set_process_name('php task_queue task worker');
        } else {
            swoole_set_process_name('php task_queue event worker');
        }
    }

    public function start(\swoole_server $server) {
        swoole_set_process_name('php task_queue master');
        //记录进程文件
        $dir = $this->_config['pidfile'];
        if (!is_dir($dir)) mkdir($dir);
        $masterPidFile = $dir.'task_queue.master.pid';
        file_put_contents($masterPidFile, $server->master_pid);

        $managerPidFile = $dir.'task_queue.manager.pid';
        file_put_contents($managerPidFile, $server->manager_pid);
    }
}
