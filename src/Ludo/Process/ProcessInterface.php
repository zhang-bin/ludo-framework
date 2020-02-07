<?php

namespace Ludo\Process;

use Swoole\Process;

interface ProcessInterface
{
    public function handle(Process $process);
}