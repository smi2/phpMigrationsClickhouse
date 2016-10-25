<?php
namespace ClickHouseDB;
class SendMigrationCluster extends Cluster
{
    public function showDebug($message,$print=false)
    {
        $message=str_ireplace(["\n","\r","\t"],'',$message);
        if ($print) echo date('H:i:s')." ".$message."\n";
    }
    public function sendMigration(Cluster\Migration $migration,$showDebug=false)
    {
        $node_hosts=$this->getClusterNodes($migration->getClusterName());

        $sql_down=$migration->getSqlDowngrade();
        $sql_up=$migration->getSqlUpdate();
        $error=[];
        // Пропингуем все хосты
        foreach ($node_hosts as $node) {
            try {
                if ($migration->getTimeout())
                {

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
                if ($need_undo)
                {
                    $undo_ip[$node]=1;
                    break;
                }
            }
            if ($need_undo)
            {
                $undo_ip[$node]=1;
                break;
            }
        }

        if (!$need_undo)
        {
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