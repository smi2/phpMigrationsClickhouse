<?php
return
[
    'clickhouse.server.1.migrations'=>
        [
            'clickhouse'=>['host'=>'x.x.net','port'=>'8123','username'=>'x','password'=>'x'],
            'repository'=>__DIR__ . '/../ClickHouseMigrations/x1x/',
            'split'=>['query'=>';;'],
            'coordinator'=>['pdo'=>['host'=>'']]
        ]
];