<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        // Основной кэш (например, Redis)
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
        ],
        // Дополнительный кэш на Memcached
        'memCache' => [
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => 'memcached',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ],
        // Компонент Redis
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'redis',
            'port' => 6379,
            'database' => 0,
        ],
    ],
];
