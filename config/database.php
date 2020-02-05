<?php
return [
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'read' => [
                'host'      => 'localhost',
                'database'  => 'sales',
                'username'  => 'root',
                'password'  => '64297881',
            ],
            'write' => [
                'host'      => 'localhost',
                'database'  => 'catsky',
                'username'  => 'root',
                'password'  => '64297881',
            ],
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
];