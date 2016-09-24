<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/phpClickHouse/include.php';
// ;



class MigrationRepo
{
    private $path;
    public function __construct($repo,$path)
    {
        $this->path=$path;
        $this->git = \Coyl\Git\Git::open($repo);
        // https://github.com/cpliakas/git-wrapper
        // https://github.com/teqneers/PHP-Stream-Wrapper-for-Git
        // http://gitonomy.com/doc/gitlib/master/api/commit/
        $this->openRepo();
    }
    private function dirMigrations()
    {
        return $this->git->getRepoPath()."/".$this->path;
    }
    private function openRepo()
    {
        $this->git->fetch();
        $this->git->pull('origin', 'master');
    }
    private function pushRepo()
    {
        if ($this->git->hasChanges()) {
            $this->git->commit('Migration done.');
            $this->git->push('origin', 'master');
        }
    }
    public function getList()
    {

    }
    public function getNext()
    {

    }
    public function getContent($hash)
    {
        //
    }
    public function setStart($hash)
    {
        $filename=$this->files[$hash]['filename'];

    }
    public function setDone($hash)
    {
        //
    }
    public function setBad($hash)
    {
        //
    }
}

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\CliMenuBuilder;

$itemCallable = function (CliMenu $menu) {
    echo $menu->getSelectedItem()->getText();
};

$menu = (new CliMenuBuilder);
$menu->setTitle('Clickhouse Tools, Select configuration')->addLineBreak('-');
$menu->addItem("Show ", $itemCallable);



$config_list = include_once 'config.php';
foreach ($config_list as $cluster_id=>$config) {
    if (!isset($config['repository'])) die('xxx1');
    if (!isset($config['clickhouse'])) die('xxx2');
    if (!isset($config['split']['query'])) die('xxx2');


    $mr = new MigrationRepo($config['repository'], $config['path']);

    $cl = new ClickHouseDB\Cluster($config['clickhouse']);
    $cl->setScanTimeOut(5);
    if (!$cl->isReplicasIsOk()) {
        throw new Exception('Replica state is bad , error=' . $cl->getError());
    }


    $menu->addItem($cluster_id, $itemCallable);
}



$menu->build()->open();


//


    // class repo , git pull
    // open dir
    // scan new file
    // make run_hash_key
    // lock coordinator
    // exec migration
    // unlock coordinator
    // fun!

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