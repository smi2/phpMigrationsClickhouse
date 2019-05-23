<?php

namespace ClickHouseDB\Cluster;
use ClickHouseDB\Exception;

/**
 * Class Query
 * @package ClickHouseDB
 */
class Query
{
    /**
     * @var string
     */
    private $cluster_name;
    /**
     * @var int
     */
    private $timeout=0;

    /**
     * @var bool
     */
    private $forceContinue=false;

    public function __construct($cluster_name)
    {
        $this->cluster_name=$cluster_name;
    }

    /**
     * @return string
     */
    public function getClusterName()
    {
        return $this->cluster_name;
    }

    public function getError()
    {

    }
    public function isOk()
    {

    }
    /**
     * @param $seconds float
     * @return $this
     */
    public function setTimeout($seconds)
    {
        $this->timeout=$seconds;
        return $this;
    }

    /**
     * @return float
     */
    public function getTimeout()
    {
        return floatval($this->timeout);
    }

    public function getNodesProcessed()
    {

    }
    public function getNodesError()
    {

    }

    /**
     * @return bool
     */
    public function isForceContinue()
    {
        return $this->forceContinue;
    }

    /**
     * @param $flag
     */
    public function forceContinue($flag)
    {
        $this->forceContinue=$flag;
    }
}

class Migration extends Query
{
    private $_sql_up=[];
    private $_sql_down=[];
    private $_split_chars=';;';
    private $_actionOnError='undo';

    private function autoSplit($sql)
    {
        if ($this->_split_chars)
        {
            return explode($this->_split_chars,$sql);
        }
        return $sql;
    }

    /**
     * @param $split_chars
     * @return $this
     */
    public function setAutoSplitQuery($split_chars)
    {
        $this->_split_chars=$split_chars;
        return $this;
    }

    /**
     * @param $sql
     * @return $this
     */
    public function addSqlUpdate($sql)
    {
        $sql=$this->autoSplit($sql);

        if (is_array($sql))
        {
          foreach ($sql as $q)
          {
              $q=trim($q);
              if ($q)
              $this->_sql_up[]=$q;
          }
        }
        else
        {
            $this->_sql_up[]=$sql;
        }
        return $this;
    }

    /**
     * @param $sql
     * @return $this
     */
    public function addSqlDowngrade($sql)
    {
        $sql=$this->autoSplit($sql);
        if (is_array($sql))
        {
            foreach ($sql as $q)
            {
                $q=trim($q);
                if ($q)
                $this->_sql_down[]=$q;
            }
        }
        else
        {
            $this->_sql_down[]=$sql;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getSqlDowngrade()
    {
        return $this->_sql_down;
    }

    /**
     * @return array
     */
    public function getSqlUpdate()
    {
        return $this->_sql_up;
    }

    /**
     * `undo` or `continue` or `break`
     *
     * @param string $action
     * @return $this
     */
    public function setErrorAction($action='undo')
    {
        $action=strtolower($action);
        if (in_array($action,['undo','break','continue']))
        {
            throw new Exception('Bad set action');
        }
        $this->_actionOnError=$action;
        return $this;
    }

    public function getErrorAction()
    {
        return $this->_actionOnError;
    }

}