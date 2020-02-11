<?php

namespace Ludo\Process;

use Swoole\Process as SwooleProcess;
use Swoole\Event;

abstract class Process implements ProcessInterface
{
    /**
     * 运行程序
     *
     * @return int
     */
    public function run()
    {
        SwooleProcess::daemon();
        $process = new SwooleProcess([$this, 'handle']);
        $pid = $process->start();
        $workers[$pid] = $process;

        SwooleProcess::signal(SIGCHLD, function ($signal) use (&$workers) {
            while (true) {
                $result = SwooleProcess::wait(false);
                debug(json_encode($result));
                if (!$result || $result['signal'] == SIGKILL) {
                    break;
                }

                $pid = $result['pid'];
                /**
                 * @var $childProcess SwooleProcess
                 */
                $childProcess = $workers[$pid];
                $newPid = $childProcess->start();
                $workers[$newPid] = $childProcess;
            }
        });

        SwooleProcess::signal(SIGTERM, function ($signal) use ($workers) {
            foreach ($workers as $pid => $process) {
                SwooleProcess::kill($pid, SIGKILL);
            }

            exit();
        });

        foreach ($workers as $process) {
            Event::add($process->pipe, function ($pipe) use ($process) {
                return;
            });
        }

        return getmypid();
    }
}