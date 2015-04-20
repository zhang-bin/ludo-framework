<?php
namespace Ludo\Task;
use Ludo\Config\Config;
use Ludo\Support\ServiceProvider;

class TaskQueueServer {
    private $_config = array();
    /**
     * @var swoole_server
     */
    private $_server;

    public function __construct() {
        swoole_set_process_name('php task_queue manager');
        $this->_config = Config::server()['server.task_queue'];
    }

    public function run() {
        $this->_server = new \swoole_server($this->_config['bind'], $this->_config['port']);
        $config = array(
            'worker_num' => $this->_config['worker_num'],
            'log_file' => $this->_config['log_file'],
            'task_worker_num' => $this->_config['task_worker_num'],
            'user' => $this->_config['user'],
            'group' => $this->_config['group'],
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
        switch ($data) {
            case 'reload':
                $server->reload();
                break;
            case 'stop':
                $server->shutdown();
                break;
            default:
                $data = json_decode($data, true);
                foreach ($data['queue'] as $item) {
                    $server->task(json_encode($item));
                }
                break;
        }
    }

    public function task(\swoole_server $server, $taskId, $fromId, $data) {
        $data = json_decode($data, true);
        $result = curlPost($data['url'], http_build_query($data['data']));
        $log = sprintf('Url: %s, Task Data: %s ---- Task Result: %s', $data['url'], json_encode($data['data'], JSON_UNESCAPED_UNICODE), $result);
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
    }
}
