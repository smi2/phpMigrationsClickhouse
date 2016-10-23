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
        $menu->setUnselectedMarker(' ')->setSelectedMarker('✏')    ->setItemExtra('✔')->addStaticItem('AREA 51');
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
            if (!$this->chcluster[$id]->isReplicasIsOk()) {
                throw new Exception('Replica state is bad , error=' . $this->chcluster[$id]->getError());
             }
        }
        return $this->chcluster[$id];
    }
    private function getArt()
    {
        return Art::getArt();


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
        echo "Update repo\n";
        $this->migrations=[];
        $this->getRepo()->reopen();
        echo "Done\n";
    }
    public function event_ExecMigration(\SplFileInfo $file,CliMenu $menu)
    {
        echo $file->getFilename().' : '.date('Y-m-d H:i:s',$file->getMTime()).' : '.$file->getSize()."\n";
        echo $file->getPathname()."\n";



        echo "\n\nsendMigration....\n";
        $this->getChCluster()->sendMigration($this->getMigration($file->getPathname()),true);
        echo "\nPress Up/Down!\n";



    }
    public function BaseAction()
    {
        echo "Open & pull git repo....\n";
        $list_files=$this->getRepo()->getList();


        echo "Connect to CH cluster....\n";
        $this->getChCluster()->getClusterList();





        $menu=$this->makeMenu('Select migration');

        $menu->addItem("Update repository", function (CliMenu $menu) {
            self::event_UpdateRepo();
        });
        foreach ($list_files as $file)
        {
              $menu->addItem($file->getFilename().' : '.date('Y-m-d H:i:s',$file->getMTime()).' : '.$file->getSize(), function (CliMenu $menu) use ($file) {
                    self::event_ExecMigration($file,$menu);
              });
        }

        $menu->build()->open();

        // class repo , git pull
        // open dir
        // scan new file
        // make run_hash_key
        // lock coordinator
        // exec migration
        // unlock coordinator
        // fun!





    }
    public function ExitAction()
    {
        echo "$> bye ByE;)\n";
    }
}
