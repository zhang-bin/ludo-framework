<?php
\Ludo\Foundation\Lang::init();
\Ludo\Config\Config::init();

$alias = [
    'Filter' => 'Ludo\Support\Filter',
    'Validator' => 'Ludo\Support\Validator',
    'Lang' => 'Ludo\Foundation\Lang',
    'Config' => 'Ludo\Config\Config',
    'Factory' => 'Ludo\Support\Factory',
    'View' => 'Ludo\View\View',
    'TaskQueue' => 'Ludo\Task\TaskQueue',
    'QueryException' => 'Ludo\Database\QueryException',
    'Counter' => 'Ludo\Counter\Counter',
];
\Ludo\Support\AliasLoader::getInstance($alias)->register();