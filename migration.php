<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/phpClickHouse/include.php';
include_once __DIR__ . '/src/include.php';


if (!is_file('config.php'))
{
    echo "no file:`config.php`";
    exit(9);
}
$config_ch_list = include_once 'config.php';



$Commander=new MigrationsClickhouse\Commander($config_ch_list);
$Commander->InitAction();
if (!$Commander->isSelectConfiguration()) {
    $Commander->ExitAction();
}
else {
    $Commander->BaseAction();
}

    // class repo , git pull
    // open dir
    // scan new file
    // make run_hash_key
    // lock coordinator
    // exec migration
    // unlock coordinator
    // fun!

exit;











//
//if ($migration instanceof ClickHouseDB\Cluster\Migration)
//{
//
//    $cluster_name=$migration->getClusterName();
//
//    echo "> $cluster_name , count shard   = ".$cl->getClusterCountShard($cluster_name)." ; count replica = ".$cl->getClusterCountReplica($cluster_name)."\n";
//
//    if (!$cl->sendMigration($mclq,true))
//    {
//        throw new Exception('sendMigration is bad , error='.$cl->getError());
//    }
//    echo "All ok!\nExit;)\n";
//    exit(0);
//}
//else
//{
//    throw new Exception("migration not load");
//}