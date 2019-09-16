<?php

namespace Ludo\Server;


class SwooleEvent
{
    const ON_START = 'start';

    const ON_SHUT_DOWN = 'shutdown';

    const ON_WORKER_START = 'workerStart';

    const ON_WORKER_STOP = 'workerStop';

    const ON_WORKER_EXIT = 'workerExit';

    const ON_CONNECT = 'connect';

    const ON_REQUEST = 'request';

    const ON_RECEIVE = 'receive';

    const ON_PACKET = 'packet';

    const ON_CLOSE = 'close';

    const ON_TASK = 'task';

    const ON_FINISH = 'finish';

    const ON_PIPE_MESSAGE = 'pipeMessage';

    const ON_HAND_SHAKE = 'handshake';

    const ON_OPEN = 'open';

    const ON_MESSAGE = 'message';

    const ON_WORKER_ERROR = 'workerError';

    const ON_MANAGER_START = 'managerStart';

    const ON_MANAGER_STOP = 'mangerStop';
}