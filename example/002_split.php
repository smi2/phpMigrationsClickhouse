<?php
$cluster_name='sharovara';

$mclq=new ClickHouseDB\Cluster\Migration($cluster_name);
$mclq->setTimeout(100.1)->setAutoSplitQuery(';;')->

addSqlUpdate('


DROP DATABASE IF EXISTS shara

;;


CREATE DATABASE IF NOT EXISTS shara


;;


DROP TABLE IF EXISTS shara.adpreview_body_views_sharded

')->addSqlDowngrade('



DROP TABLE IF EXISTS shara.adpreview_body_views


;;


DROP TABLE IF EXISTS shara.adpreview_body_views_sharded

');

$mclq->addSqlDowngrade('DROP DATABASE IF EXISTS shara');

return $mclq;