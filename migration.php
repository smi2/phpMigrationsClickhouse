<?php
include_once __DIR__ . '/phpClickHouse/include.php';
// ;

$config_list = include_once 'config.php';
foreach ($config_list as $cluster_id=>$config)
{
    if (!isset($config['repository'])) die('xxx1');
    if (!isset($config['clickhouse'])) die('xxx2');
    if (!isset($config['split']['query'])) die('xxx2');



    $cl = new ClickHouseDB\Cluster($config['clickhouse']);
    $cl->setScanTimeOut(5);
    if (!$cl->isReplicasIsOk())
    {
        throw new Exception('Replica state is bad , error='.$cl->getError());
    }

    // class repo , git pull
    // open dir
    // scan new file
    // make run_hash_key
    // lock coordinator
    // exec migration
    // unlock coordinator
    // fun!
}

exit;












$migration=include 'example/001_add_some.php';

if ($migration instanceof ClickHouseDB\Cluster\Migration)
{

    $cluster_name=$migration->getClusterName();

    echo "> $cluster_name , count shard   = ".$cl->getClusterCountShard($cluster_name)." ; count replica = ".$cl->getClusterCountReplica($cluster_name)."\n";

    if (!$cl->sendMigration($mclq,true))
    {
        throw new Exception('sendMigration is bad , error='.$cl->getError());
    }
    echo "All ok!\nExit;)\n";
    exit(0);
}
else
{
    throw new Exception("migration not load");
}