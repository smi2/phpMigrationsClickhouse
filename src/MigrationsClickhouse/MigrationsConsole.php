<?php
class MigrationsConsole
{
    private $config_ch_list=[];
    public function setConfig()
    {
        //
    }
    private function initConfigCH()
    {
        $config='config.php';
        if (!is_file($config))
        {
            echo "no file:`config.php`";
            exit(9);
        }
        $this->config_ch_list = include_once 'config.php';
    }
    /**
     * Запустить выбор миграции
     *
     * @param string $select SelectConfiguration
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