<?php


if (php_sapi_name() != 'cli') {
    die('Must run from command line');
}
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
ini_set('log_errors', 0);
ini_set('html_errors', 0);

include_once __DIR__ . '/src/shell/include.php';
include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/phpClickHouse/include.php';
include_once __DIR__ . '/src/include.php';


Shell::name("MigrationsClickhouse");
Shell::dir(__DIR__);
Shell::maxExecutionMinutes(30);//30 mins

Shell::run(
    new MigrationsConsole()
);



exit;

