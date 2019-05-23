<?php
namespace ClickHouseDB;
class SendMigrationCluster extends Cluster
{
    public function showDebug($message,$print=false)
    {
        $message=str_ireplace(["\n","\r","\t"],'',$message);
        if ($print)
        {
            \Shell::msg($message);
        }
    }
    public function dropOld(\ClickHouseDB\Cluster $cluster,$database,$table,$days=160)
    {
        $node_hosts=$cluster->getClusterNodes('ads');
        foreach ($node_hosts as $node) {
            $this->client($node)->ping();
            $this->showDebug("client($node)->ping() OK!",true);
        }
        $nodes=$cluster->getMasterNodeForTable($database.'.'.$table);
        // scan need node`s
        shuffle($nodes);
        foreach ($nodes as $node)
        {
            $this->client($node)->database($database)->setTimeout(2000);
            $this->showDebug("client($node)->start",true);
            $ret=$this->dropOldPartitions($node,$table,$days,215);
        }
    }


    public function dropOldPartitions($node, $table_name, $days_ago, $count_partitons_per_one = 100)
    {
        $days_ago = strtotime(date('Y-m-d 00:00:00', strtotime('-' . $days_ago . ' day')));

        $drop = [];
        $client=$this->client($node);
        $list_patitions = $client->partitions($table_name, 20000);

        foreach ($list_patitions as $partion_id => $partition) {
            if (stripos($partition['engine'], 'mergetree') === false) {
                continue;
            }

            $max_date = strtotime($partition['max_date']);

            if ($max_date < $days_ago) {
                $drop[$partition['partition']] = $partition['partition'];
            }
        }
        $c=0;
        ksort($drop);
        foreach ($drop as $partition_id) {
            $c++;
            if ($c>$count_partitons_per_one) break;
            $sql="ALTER TABLE $table_name DROP PARTITION $partition_id";

            echo "\nsudo touch '/opt/clickhouse/flags/force_drop_table' && sudo chmod 666 '/opt/clickhouse/flags/force_drop_table' && clickhouse-client --database=ads --password=w8z6QtXEzA0ohgdw --user=model --query=\"ALTER TABLE block_views_sharded DROP PARTITION $partition_id\" \n\n";
//            $this->showDebug("client($node)\t$sql",true);
//            $client->write($sql);
            //

        }
//        foreach ($drop as $partition_id) {
//            $this->dropPartition($table_name, $partition_id);
//        }

        return $drop;
    }


    public function sendQuery(\ClickHouseDB\Cluster $cluster,$sql)
    {
        $node_hosts=$cluster->getClusterNodes('ads');
        foreach ($node_hosts as $node) {
            $this->client($node)->ping();
            $this->showDebug("client($node)->ping() OK!",true);
        }
        foreach ($node_hosts as $node) {
            $st=$this->client($node)->select($sql);
            echo ">$node>\n";
            echo json_encode($st->rows());
            echo "\n\n";

        }
    }
    public function sendMigration(Cluster\Migration $migration,$showDebug=false)
    {
        $node_hosts=$this->getClusterNodes($migration->getClusterName());

        $sql_down=$migration->getSqlDowngrade();
        $sql_up=$migration->getSqlUpdate();

        $isForceContinue=$migration->isForceContinue();



        $error=[];
        // Пропингуем все хосты
        foreach ($node_hosts as $node) {
            try {
                if ($migration->getTimeout())
                {

                    $this->client($node)->settings()->set('replication_alter_columns_timeout',$migration->getTimeout());
                    $this->client($node)->settings()->max_execution_time($migration->getTimeout());
                }
                $this->client($node)->ping();
                $this->showDebug("client($node)->ping() OK!",$showDebug);

            } catch (QueryException $E) {

                $this->showDebug("Can`t connect or ping ip/node : " . $node,$showDebug);
                $error[] = "Can`t connect or ping ip/node : " . $node;
                return false;
            }
        }
        if ($isForceContinue) {
            $this->showDebug("WARNING: isForceContinue!",$showDebug);
        }


        // Выполняем запрос на каждый client(IP) , если хоть одни не отработал то делаем на каждый Down
        $need_undo=false;
        $undo_ip=[];
        foreach ($sql_up as $s_u)
        {
            foreach ($node_hosts as $node) {
                try {
                    $this->showDebug("client($node)->write(".substr($s_u,0,45).")....",$showDebug);

                    if ($this->client($node)->write($s_u)->isError()) {
                        $need_undo = true;
                        $error[] = "Host $node result error";
                        $this->showDebug("client($node)->Host $node result error",$showDebug);
                    }
                    else
                    {
                        // OK
                    }
                } catch (QueryException $E) {
                    $need_undo = true;
                    $this->showDebug("client($node)->Host $node result error:".$E->getMessage(),$showDebug);
                    $error[] = "Host $node result error : " . $E->getMessage();
                }
                if ($need_undo && !$isForceContinue)
                {
                    $undo_ip[$node]=1;
                    break;
                }
            }
            if ($need_undo && !$isForceContinue)
            {
                $undo_ip[$node]=1;
                break;
            }
        }

        if (!$need_undo)
        {
            return true;
        }
        if ($isForceContinue) {
            return true;
        }

        // if Undo
        // тут не очень точный метод отката
//        foreach ($undo_ip as $node=>$tmp)
        foreach ($node_hosts as $node)
        {
            foreach ($sql_down as $s_u) {
                $this->showDebug("undo_client($node)->write(".substr($s_u,0,45).")...",$showDebug);

                try{
                    $st=$this->client($node)->write($s_u);
                }
                catch (Exception $E) {
                    $this->showDebug("!!!!!!  error(".substr($s_u,0,45).")...".$E->getMessage(),$showDebug);
                }
            }
        }
        return false;

    }
}