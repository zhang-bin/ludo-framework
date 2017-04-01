<?php
\Ludo\Foundation\Lang::init();
\Ludo\Config\Config::init();

$alias = [
    'Filter' => Ludo\Support\Filter::class,
    'Validator' => Ludo\Support\Validator::class,
    'Lang' => Ludo\Foundation\Lang::class,
    'Config' => Ludo\Config\Config::class,
    'Factory' => Ludo\Support\Factory::class,
    'View' => Ludo\View\View::class,
    'TaskQueue' => Ludo\Task\TaskQueue::class,
    'QueryException' => Ludo\Database\QueryException::class,
    'Counter' => Ludo\Counter\Counter::class,
    'Encrypter' => Ludo\Encrypter\Encrypter::class,
];
\Ludo\Support\AliasLoader::getInstance($alias)->register();

