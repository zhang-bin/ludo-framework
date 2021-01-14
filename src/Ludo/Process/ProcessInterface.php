<?php

namespace Ludo\Process;

use Swoole\Process;


/**
 * Process Interface
 *
 * @package Ludo\Process
 */
interface ProcessInterface
{
    /**
     * Process main handle
     *
     * @param Process $process process object
     * @return mixed
     */
    public function handle(Process $process);
}