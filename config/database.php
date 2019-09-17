<?php
return array(
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'read' => array(
                'host'      => 'localhost',
                'database'  => 'sales',
                'username'  => 'root',
                'password'  => '64297881',
            ),
            'write' => array(
                'host'      => 'localhost',
                'database'  => 'catsky',
                'username'  => 'root',
                'password'  => '64297881',
            ),
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],
        'mysql2' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'catsky',
            'username'  => 'root',
            'password'  => '64297881',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],
    ]
);