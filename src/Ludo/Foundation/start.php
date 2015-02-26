<?php
\Ludo\Foundation\Lang::init();
\Ludo\Config\Config::init();

$alias = array(
    'Filter' => 'Ludo\Support\Filter',
    'Validator' => 'Ludo\Support\Validator',
    'Lang' => 'Ludo\Foundation\Lang',
    'Config' => 'Ludo\Config\Config',
    'Factory' => 'Ludo\Support\Factory',
    'View' => 'Ludo\View\View'
);
\Ludo\Support\AliasLoader::getInstance($alias)->register();