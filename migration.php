<?php
include_once __DIR__ . '/phpClickHouse/include.php';

$config = include_once __DIR__ . '/../_clickhouse_config_product_2.php';

$cl = new ClickHouseDB\Cluster($config);

$cl->setScanTimeOut(2.5); // 2500 ms
if (!$cl->isReplicasIsOk())
{
    throw new Exception('Replica state is bad , error='.$cl->getError());
}
//


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