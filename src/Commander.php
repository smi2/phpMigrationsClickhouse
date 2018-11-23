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
    private $migrations=[];

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
     * @return CliMenuBuilder
     */
    public function makeMenu($Title)
    {
        $menu = (new CliMenuBuilder);
        $menu->setTitle('Clickhouse Tools:'.$Title)->addLineBreak('-') ->setWidth(300);

        $menu->addAsciiArt($this->getArt(),'left');
        $menu->setUnselectedMarker(' ')->setSelectedMarker('✏')    ->setItemExtra('✔')->addStaticItem('ClickHouse Migrations');
        return $menu;
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
            $this->chcluster[$id]=new \ClickHouseDB\SendMigrationCluster($config['clickhouse']);
            $this->chcluster[$id]->setScanTimeOut(15);
            $this->chcluster[$id]->rescan();
            if (!$this->chcluster[$id]->isReplicasIsOk()) {
                throw new Exception('Replica state is bad , error= ' . json_encode($this->chcluster[$id]->getError()));
             }
        }
        return $this->chcluster[$id];
    }
    private function getArt()
    {
        return Art::getArt();


    }
    public function DropAction($cluster,$database,$table)
    {
        $this->SelectConfiguration($cluster);
        \Shell::msg("$database,$table");
        $this->getChCluster()->dropOld($this->getChCluster(),$database,$table);
    }
    public function queryAction($sql,$cluster)
    {
        $this->SelectConfiguration($cluster);
        \Shell::msg("$sql");
        $this->getChCluster()->sendQuery($this->getChCluster(),$sql);
    }
    public function InitAction()
    {
        if ($this->isSelectConfiguration()) return false;
        $menu=$this->makeMenu('Select configuration');
        foreach ($this->configs_ch as $cluster_id=>$config) {
            $item_title=$cluster_id." : ".$config['clickhouse']['host'];
            $menu->addItem($item_title, function (CliMenu $menu) use ($cluster_id) {
                self::SelectConfiguration($cluster_id);
                $menu->close();
            });
        }
        $menu->build()->open();
    }

    private function getMigration($pathname)
    {

        if (!isset($this->migrations[$pathname]))
        {
            $this->migrations[$pathname]=include $pathname;
        }
        return $this->migrations[$pathname];
    }

    public function event_UpdateRepo()
    {
        \Shell::msg("Update repo....");
        \Shell::info("Update repo....");
        $this->migrations=[];
        $this->getRepo()->reopen();
        \Shell::msg("Done");
    }
    public function event_ExecMigration(\SplFileInfo $file,CliMenu $menu)
    {
        \Shell::msg("ExecMigration");
        \Shell::msg($file->getFilename().' : '.date('Y-m-d H:i:s',$file->getMTime()).' : '.$file->getSize());
        \Shell::msg("File:",$file->getPathname());
        \Shell::msg("sendMigration...",[\Shell::bold,\Shell::info]);
        $this->getChCluster()->sendMigration($this->getMigration($file->getPathname()),true);

        if (\Shell::message()->confirm("Exit?"))
        {
            $menu->closeThis();
        }
        \Shell::msg("Press Up or Down");


    }
    public function BaseAction()
    {
        \Shell::msg("Connect to CH cluster....",[\Shell::bold,\Shell::info]);
        $this->getChCluster()->getClusterList();

        \Shell::msg("Open & pull git repo....",[\Shell::bold,\Shell::info]);
        $list_files=$this->getRepo()->getList();

        $menu=$this->makeMenu('Select migration');

        $menu->addItem("Update repository", function (CliMenu $menu) {
            self::event_UpdateRepo();
        });
        foreach ($list_files as $file)
        {
              $menu->addItem($file->getFilename()."       ".date('Y-m-d H:i:s',$file->getMTime()).' ', function (CliMenu $menu) use ($file) {
                  self::event_ExecMigration($file,$menu);

              });
        }

        $menu->build()->open();

        // make run_hash_key
        // lock coordinator
        // exec migration
        // unlock coordinator
        // fun!

    }
    public function ExitAction()
    {
        \Shell::message()->message("<yellow>$> bye ByE;)</yellow>",[\Shell::bold]);
    }
}
