<?php
ini_set('date.timezone','Europe/Moscow');

return
[
    'clickhouse.server.1.migrations'=>
        [
            'clickhouse'=>['host'=>'x.x.net','port'=>'8123','username'=>'x','password'=>'x'],
            'repository'=>__DIR__ . '/../ClickhouseMigrations/',
            'path'=>'ch.cluster1',
            'split'=>['query'=>';;'],
            'coordinator'=>['pdo'=>['host'=>'']]
        ]
];