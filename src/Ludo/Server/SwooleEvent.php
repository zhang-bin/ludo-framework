<?php

namespace Ludo\Server;


/**
 * Class SwooleEvent
 *
 * @package Ludo\Server
 */
class SwooleEvent
{
    const string ON_START = 'start';

    const string ON_SHUT_DOWN = 'shutdown';

    const string ON_WORKER_START = 'workerStart';

    const string ON_WORKER_STOP = 'workerStop';

    const string ON_WORKER_EXIT = 'workerExit';

    const string ON_CONNECT = 'connect';

    const string ON_REQUEST = 'request';

    const string ON_RECEIVE = 'receive';

    const string ON_PACKET = 'packet';

    const string ON_CLOSE = 'close';

    const string ON_TASK = 'task';

    const string ON_FINISH = 'finish';

    const string ON_PIPE_MESSAGE = 'pipeMessage';

    const string ON_HAND_SHAKE = 'handshake';

    const string ON_OPEN = 'open';

    const string ON_MESSAGE = 'message';

    const string ON_WORKER_ERROR = 'workerError';

    const string ON_MANAGER_START = 'managerStart';

    const string ON_MANAGER_STOP = 'mangerStop';
}