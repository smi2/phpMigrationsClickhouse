<?php
namespace MigrationsClickhouse;

class GitRepo
{
    private $files=[];
    private $path;
    public function __construct($repo,$path)
    {
        $this->path=$path;
        $this->git = \Coyl\Git\Git::open($repo);
        // https://github.com/cpliakas/git-wrapper
        // https://github.com/teqneers/PHP-Stream-Wrapper-for-Git
        // http://gitonomy.com/doc/gitlib/master/api/commit/
        $this->openRepo();
        $this->scanDir();
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
    public function getDir()
    {
        $dir=rtrim($this->git->getRepoPath(),'/').'/'.$this->path;
        return $dir;
    }
    public function getList()
    {
        return $this->files;
    }
    public function scanDir()
    {
        $this->files=[];
        $dir=$this->getDir();
        $directory = new \RecursiveDirectoryIterator($dir);
        $flattened = new \RecursiveIteratorIterator($directory);

        // @todo : normal rexexp
        $files = new \RegexIterator($flattened, '#^(?:[A-Z]:)?(?:/(?!\.Trash)[^/]+)+/[^/]+\.(?:php)$#Di');

        foreach($files as $file) {
            $this->files[]=$file;
        }

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