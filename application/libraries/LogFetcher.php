<?php

class LogFetcher
{
    private $tableName;
    private $dataSource;
    private $databaseHandle;

    function __construct($environment = ENV_DEV, $table_name = "")
    {
        $this->dataSource = new Table($table_name, $environment);
        $this->databaseHandle = $this->dataSource->getDatabaseHandle();
        $this->tableName = $this->dataSource->getTableName();
    }

    function fetch($select, $where, $page, $page_size, $filter, $filterable, $search, $start_date, $end_date, $column_for_counting = COL_USER_ID)
    {
        $overall_where = $where;
        $perform_filtering = false;
        if (is_array($filter)) {
            foreach ($filter as $column => $value) {
                if (isset($filterable[strtolower(camelCaseToSnakeCase($column))])) {
                    $possible_filter_values = $filterable[strtolower(camelCaseToSnakeCase($column))];
                    if(FILTER_API_DRIVEN === $possible_filter_values || (is_array($possible_filter_values) && in_array($value, $possible_filter_values))){
                        $overall_where[strtolower(camelCaseToSnakeCase($column))] = $value;
                        $perform_filtering = true;
                    }
                }
            }
        }
        $searchable = array();
        if (false !== $search) {
            $searchable = array_diff($select, array_keys($filterable));
        }

        $response = array();
        $recognised_filters = array();
        foreach ($filterable as $column => $values) {
            $recognised_filters[snakeCaseToCamelCase($column)] = $values;
        }
        $response["actionableFilters"] = $recognised_filters;
        $response["overallRecordCount"] = $response["filteredRecordCount"] = $this->getCountOfAllLogs($column_for_counting, $where);

        $response["logs"] = $this->getLogs($select, $overall_where, $page_size, $page_size * ($page - 1), $searchable, $search, $start_date, $end_date);

        if ($searchable || true === $perform_filtering || false !== $start_date || false !== $end_date) {
            $response["filteredRecordCount"] = $this->getCountOfFilteredLogs($column_for_counting, $overall_where, $start_date, $end_date, $searchable, $search);
        }
        return $response;
    }

    private function getLogs($select, $where, $length_of_records, $starting_point, $searchable, $search, $start_date, $end_date)
    {
        $timestamp_column = COL_DATE_CREATED;
        $selectable = array_merge($select, array($timestamp_column));
        sort($selectable);
        $this->databaseHandle->select($selectable);
        $this->databaseHandle->where($where);
        if (false !== $start_date) {
            $this->confirmDateConformity($start_date);
            $this->databaseHandle->where("date($timestamp_column) >= '$start_date'");
        }

        if (false !== $end_date) {
            $this->confirmDateConformity($end_date);
            $this->databaseHandle->where("date($timestamp_column) <= '$end_date'");
        }
        $this->databaseHandle->order_by("$timestamp_column DESC");
        $this->databaseHandle->limit($length_of_records, $starting_point);
        if ($searchable) {
            $this->databaseHandle->group_start();
            foreach ($searchable as $index => $column) {
                if (0 == $index) {
                    $this->databaseHandle->like("UPPER($column)", strtoupper($search));
                    continue;
                }
                $this->databaseHandle->or_like("UPPER($column)", strtoupper($search));
            }
            $this->databaseHandle->group_end();
        }

        $query = $this->databaseHandle->get($this->tableName);
        $data = $query->result_array();
        foreach ($selectable as $index => $value) {
            $selectable[$index] = snakeCaseToCamelCase($value);
        }
        $readable_data = array();
        foreach ($data as $record) {
            ksort($record);
            $readable_data[] = array_combine($selectable, array_values($record));
        }
        return $readable_data;
    }

    private function getCountOfAllLogs($column, $where)
    {
        $total_count = $this->dataSource->read(array("select" => "count($column) count", "where" => $where));
        return $total_count ? $total_count[0]["count"] : 0;
    }

    private function getCountOfFilteredLogs($column, $where, $start_date, $end_date, $searchable, $search)
    {
        $timestamp_column = COL_DATE_CREATED;
        $this->databaseHandle->select("count($column) count");
        $this->databaseHandle->where($where);
        if (false !== $start_date) {
            $this->databaseHandle->where("date($timestamp_column) >= '$start_date'");
        }

        if (false !== $end_date) {
            $this->databaseHandle->where("date($timestamp_column) <= '$end_date'");
        }

        if ($searchable) {
            $this->databaseHandle->group_start();
            foreach ($searchable as $index => $column) {
                if (0 == $index) {
                    $this->databaseHandle->like("UPPER($column)", strtoupper($search));
                    continue;
                }
                $this->databaseHandle->or_like("UPPER($column)", strtoupper($search));
            }
            $this->databaseHandle->group_end();
        }

        $query = $this->databaseHandle->get($this->tableName);
        $data = $query->result_array();
        return $data ? $data[0]["count"] : 0;
    }

    private function confirmDateConformity($date)
    {
        $check_date = DateTime::createFromFormat("Y-m-d", $date);
        if ($check_date === false) {
            respond(array(status => STATUS_FAILED_DEPENDENCY, message => "Wrong date format. Hint: 1732-03-31"));
        }
    }

}