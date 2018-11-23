<?php
class MigrationsConsole
{
    private $config_ch_list=[];
    private $config='config.php';
    public function setConfig($file)
    {
        $this->config=$file;
    }
    private function initConfigCH()
    {

        if (!is_file($this->config))
        {
            echo "no file:`".$this->config."`";
            exit(9);
        }
        $this->config_ch_list = include_once $this->config;
    }

    /**
     * Запустить миграцию указанную в file
     *
     * @param $file string имя файла
     * @return bool
     */
    public function queryCommand($sql,$cluster)
    {

        $this->initConfigCH();
        $Commander=new MigrationsClickhouse\Commander($this->config_ch_list);

        $Commander->queryAction($sql,$cluster);
        return true;
    }

    public function dropCommand($cluster,$database,$table)
    {
        //$cluster,$db,$table,$date
        $this->initConfigCH();
        $Commander=new MigrationsClickhouse\Commander($this->config_ch_list);
        $Commander->DropAction($cluster,$database,$table);
    }

    /**
     * Запустить выбор миграции
     *
     * @param string $select Select Configuration Id
     * @param string $nopull No git pull
     * @return string
     */
    public function runCommand($select='')
    {
        $this->initConfigCH();
        $Commander=new MigrationsClickhouse\Commander($this->config_ch_list);
        if ($select)
        {
            $Commander->SelectConfiguration($select);
        }

        $Commander->InitAction();
        if (!$Commander->isSelectConfiguration()) {
            $Commander->ExitAction();
        }
        else {
            $Commander->BaseAction();
        }


        return 'OK!';
    }
}