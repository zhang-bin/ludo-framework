<?php

namespace Ludo\Process;

use Swoole\Process as SwooleProcess;
use Swoole\Event;


/**
 * Class Process
 *
 * @package Ludo\Process
 */
abstract class Process implements ProcessInterface
{

    /**
     * @var array $workers worker process
     */
    private array $workers = [];

    /**
     * 运行程序
     *
     * @return int
     */
    public function run(): int
    {
        SwooleProcess::daemon();
        $process = new SwooleProcess([$this, 'handle']);
        $pid = $process->start();
        $this->workers[$pid] = $process;

        SwooleProcess::signal(SIGCHLD, function ($signal) {
            while (true) {
                $result = SwooleProcess::wait(false);
                if (!$result || $result['signal'] == SIGKILL) {
                    break;
                }

                $pid = $result['pid'];
                /**
                 * @var $childProcess SwooleProcess
                 */
                $childProcess = $this->workers[$pid];
                unset($this->workers[$pid]);
                $newPid = $childProcess->start();
                $this->workers[$newPid] = $childProcess;
            }
        });

        SwooleProcess::signal(SIGTERM, function ($signal) {
            foreach ($this->workers as $pid => $process) {
                SwooleProcess::kill($pid, SIGKILL);
            }

            exit();
        });

        foreach ($this->workers as $process) {
            Event::add($process->pipe, function ($pipe) use ($process) {

            });
        }

        return getmypid();
    }
}