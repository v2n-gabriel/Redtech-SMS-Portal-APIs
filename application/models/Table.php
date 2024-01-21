<?php
/**
 * Created by PhpStorm.
 * User: GABRIEL
 * Date: 03/07/2018
 * Time: 09:45
 */

class Table extends MY_Model
{
    private $baseTableName;
    function __construct($table_name = null, $environment = ENV_DEV, $database_config_name = "mysql_redtech.174"){
        $this->baseTableName = ENV_PROD === $environment ? TABLE_PREFIX : TABLE_PREFIX_DEV;
        if(false === $environment){
            $this->baseTableName = "";
        }
        parent::__construct($this->baseTableName.$table_name, $database_config_name);
    }

    function create($data, $batch = false){
        return parent::create($data, $batch);
    }

    function read($data = null){
        return parent::read($data);
    }

    function update($condition, $data){
        return parent::update($condition, $data);
    }
    
    function executeTableQuery($sql,$result = false){
        $sql = str_replace("{table}", $this->tableName, str_replace("{env}", $this->baseTableName, $sql));
        return parent::executeQuery($sql, $result);
    }

    function executeQuery($sql, $result = false){
        return parent::executeQuery($sql, $result);
    }

    function getAffectedRows() {
        return parent::getAffectedRows();
    }

    function getLastQuery() {
        return parent::getLastQuery();
    }

    function getDatabaseHandle(){
        return parent::getDatabaseHandle();
    }

    function getTableName()
    {
        return parent::getTableName();
    }

}
