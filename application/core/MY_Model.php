<?php

/**
 * Created by PhpStorm.
 * User: GABRIEL
 * Date: 23/05/2018
 * Time: 11:49
 */
class MY_Model extends CI_Model {

    protected $tableName;
    private $database;

    function __construct($table_name, $database_config_name = "mysql_redtech.174") {
        parent::__construct();
        $this->tableName = $table_name;
        $this->database = "mysql_redtech.174" === $database_config_name ? $this->db : $this->load->database($database_config_name, true);
    }

    protected function create($data, $batch = false) {
        try {
            if ($batch) {
                $result = $this->database->insert_batch($this->tableName, $data);
            } else {
                $result = $this->database->insert($this->tableName, $data);
            }
        } catch (Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

    protected function read($data = null) {
        if ($data != null & is_array($data)) {
            foreach ($data as $method => $parameters) {
                if (method_exists($this->database, $method)) {
                    if ($method == "like") {
                        $this->database->like($parameters[0], $parameters[1]);
                    } else {
                        $this->database->$method($parameters);
                    }
                }
            }
        }
        $query = $this->database->get($this->tableName);
        $result = $query->result_array();
        return $result;
    }

    protected function update($condition, $data) {
        try {
            $this->database->set($data);
            if ($condition) {
                $this->database->where($condition);
            }
            $result = $this->database->update($this->tableName, $data);
        } catch (Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

    protected function executeQuery($sql, $result = false) {
        $query = $this->database->query($sql);
        return $result ? $query->result_array() : $query;
    }

    protected function getAffectedRows() {
        return $this->database->affected_rows();
    }

    function reconfigureDB($attribute, $value) {
        $this->database->$attribute = $value;#todo: check is prop $attibute exist
    }
    protected function getLastQuery(){
        return $this->database->last_query();
    }

    protected function getDatabaseHandle(){
        return $this->database;
    }

    protected function getTableName(){
        return $this->tableName;
    }
}
