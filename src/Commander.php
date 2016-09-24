<?php
namespace MigrationsClickhouse;
use ClickHouseDB\Exception;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\CliMenuBuilder;

class Commander
{
    private $chcluster=[];
    private $repo=[];
    private $configs_ch=[];
    private $select_id=false;
    public function __construct($config_ch_list)
    {
        foreach ($config_ch_list as $cluster_id=>$config) {
            if (!isset($config['repository'])) die('xxx12');
            if (!isset($config['clickhouse'])) die('xxx13');
            if (!isset($config['split']['query'])) die('xxx23');
            if (!isset($config['clickhouse']['host'])) die('xxx23');
            if (!isset($config['clickhouse']['port'])) die('xxx23');
            $this->configs_ch[$cluster_id] = $config;
        }
    }

    public function getSelectConfigurationId()
    {
        $this->getSelectConfiguration();
        return $this->select_id;
    }
    public function getSelectConfiguration()
    {
        if (!$this->select_id) throw new Exception("Not select ID");
        if (empty($this->configs_ch[$this->select_id]['clickhouse'])) throw new Exception("Not config for select ID");
        return $this->configs_ch[$this->select_id];
    }
    public function isSelectConfiguration()
    {
        return ($this->select_id?true:false);
    }
    public function SelectConfiguration($id)
    {
        $this->select_id=$id;
    }

    /**
     * @return \MigrationsClickhouse\GitRepo
     * @throws Exception
     */
    protected function getRepo()
    {
        $config=$this->getSelectConfiguration();
        $id=$this->getSelectConfigurationId();

        if (empty($this->repo[$id]))
        {
            $this->repo[$id]=new \MigrationsClickhouse\GitRepo($config['repository'], $config['path']);
        }
        return $this->repo[$id];
//
    }

    /**
     * @return \ClickHouseDB\Cluster
     * @throws Exception
     */
    protected function getChCluster()
    {
        $config=$this->getSelectConfiguration();
        $id=$this->getSelectConfigurationId();
        if (empty($this->chcluster[$id]))
        {
            $this->chcluster[$id]=new \ClickHouseDB\Cluster($config['clickhouse']);
            $this->chcluster[$id]->setScanTimeOut(15);
            if (!$this->chcluster[$id]->isReplicasIsOk()) {
                throw new Exception('Replica state is bad , error=' . $this->chcluster[$id]->getError());
             }
        }
        return $this->chcluster[$id];
    }
    private function getArt()
    {
        return <<<ART

 _____ _   _____         _
|     | |_|_   _|___ ___| |___
|   --|   | | | | . | . | |_ -|
|_____|_|_| |_| |___|___|_|___|

ART;

    }
    public function InitAction()
    {
        $menu = (new CliMenuBuilder);
        $menu->addAsciiArt($this->getArt());
        $menu->setTitle('Clickhouse Tools, Select configuration')->addLineBreak('-');
        $menu->setUnselectedMarker(' ')->setSelectedMarker('✏')    ->setItemExtra('✔');
        foreach ($this->configs_ch as $cluster_id=>$config) {
            $item_title=$cluster_id." : ".$config['clickhouse']['host'];
            $menu->addItem($item_title, function (CliMenu $menu) use ($cluster_id) {
                self::SelectConfiguration($cluster_id);
                $menu->close();
            });
        }
        $menu->build()->open();
    }
    public function BaseAction()
    {
        echo "Open repo!\n";
        $this->getRepo()->getList();
        echo "Open connect to CH\n";
        $this->getChCluster()->getClusterList();

    }
    public function ExitAction()
    {
        echo "$> bye ByE;)\n";
    }
}
