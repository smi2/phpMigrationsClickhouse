<?php
$cluster_name='sharovara';

$mclq=new ClickHouseDB\Cluster\Migration($cluster_name);
$mclq->addSqlUpdate('DROP DATABASE IF EXISTS shara');
$mclq->addSqlUpdate('CREATE DATABASE IF NOT EXISTS shara');
$mclq->addSqlUpdate('DROP TABLE IF EXISTS shara.adpreview_body_views_sharded');
$mclq->addSqlUpdate('DROP TABLE IF EXISTS shara.adpreview_body_views');

$mclq->addSqlUpdate(
    "CREATE TABLE IF NOT EXISTS shara.adpreview_body_views_sharded (
    event_date Date DEFAULT toDate(event_time),
    event_time DateTime DEFAULT now(),
    body_id Int32,
    site_id Int32,
    views Int32
) ENGINE = ReplicatedSummingMergeTree('/clickhouse/tables/{sharovara_replica}/shara/adpreview_body_views_sharded', '{replica}', event_date, (event_date, event_time, body_id, site_id), 8192)
");
$mclq->addSqlUpdate(
    "CREATE TABLE IF NOT EXISTS 
shara.adpreview_body_views AS shara.adpreview_body_views_sharded 
ENGINE = Distributed(sharovara, shara, adpreview_body_views_sharded , rand())
");

// откат
$mclq->addSqlDowngrade('DROP TABLE IF EXISTS shara.adpreview_body_views');
$mclq->addSqlDowngrade('DROP TABLE IF EXISTS shara.adpreview_body_views_sharded');
$mclq->addSqlDowngrade('DROP DATABASE IF EXISTS shara');

return $mclq;