<?php
if (php_sapi_name() != 'cli') {
    die('Must run from command line');
}
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
ini_set('log_errors', 0);
ini_set('html_errors', 0);


include_once 'MigrationsClickhouse/GitRepo.php';
include_once 'ClusterQuery/ClusterQuery.php';
include_once 'ClusterQuery/SendMigration.php';
include_once 'Commander.php';
include_once 'Art.php';