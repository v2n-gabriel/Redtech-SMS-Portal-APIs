<?php

class UserBillerLib
{
    var $userBillerTable;
    private $ciInstance,$environment,$vas2nets;

    function __construct($environment = ENV_DEV)
    {
        $this->environment = $environment;
        $this->ciInstance = & get_instance();
        $this->ciInstance->load->model("Table");
        $this->userBillerTable = new Table(TABLE_USER_BILLER, $environment);
        $this->vas2nets = new VAS2NetsB2BPlatform($this->environment);
    }

    function getUserBillers($user_id, $biller_id = false, $check_for_active_status = false){
        $id_column = COL_ID;
        $biller_id_column = COL_BILLER_ID;
        $user_id_column = COL_USER_ID;
        $columns_params = array($id_column, COL_COMMISSION, "{table}.".COL_STATUS, COL_CATEGORY,
            COL_NAME, COL_BILLER_INFO, $user_id_column, COL_BOUQUET, COL_BILLER_IMAGE);

        $columns = implode(", ", $columns_params);
        $where = array();
        if(false !== $user_id){
            $where[] = "{table}.{$user_id_column} = '$user_id'";
        }
        if(false !== $biller_id){
            $where[] = COL_BILLER_ID. " = '$biller_id'";
        }

        $sql = <<<SQL
select {$columns} from {table} join {env}{billerTable} on {env}{billerTable}.{$id_column} = {table}.{$biller_id_column}
SQL;
        if($where){
            $sql .= " where " . implode(" and ", $where);
        }

        if(true === $check_for_active_status){
            $sql .= " and {table}." . COL_STATUS . " = '".STATUS_ACTIVE."'";
        }
        $user_billers = $this->userBillerTable->executeTableQuery(str_replace("{billerTable}", TABLE_BILLER, $sql), true);
        $billers = array();
        foreach ($user_billers as $user_biller){
            $biller_id = $user_biller[COL_ID];
            $billers[$biller_id] = array("userId" => $user_biller[COL_USER_ID], "id" => $biller_id, "category" => $user_biller[COL_CATEGORY],
                "name" => $user_biller[COL_NAME], "info" => $user_biller[COL_BILLER_INFO],
                "commission" => $user_biller[COL_COMMISSION], "isBouquetService" => $user_biller[COL_BOUQUET],
                "imageUrl" => site_url("images/" . $user_biller[COL_CATEGORY] . "/" . $user_biller[COL_BILLER_IMAGE]), "status" => $user_biller[COL_STATUS]);
        }
        return $billers;
    }

    function configureOrUpdateUserBiller($payload){
        $biller_id = $payload["billerId"];
        $user_id = $payload["userId"];
        $possible_params = array(COL_COMMISSION => "commission", COL_STATUS => status);
        $update = array();
        foreach ($possible_params as $column_name => $possible_param) {
            if (isset($payload[$possible_param])) {
                $update[$column_name] = $payload[$possible_param];
            }
        }
        $response = array(status => STATUS_FAILED_DEPENDENCY, message => "$biller_id biller has not been created");
        if (isset($update[status]) && !in_array($update[status], array(STATUS_DISABLED, STATUS_ACTIVE))) {
            return array(status => STATUS_FAILED_DEPENDENCY, message => "status can only be either Active or Disabled");
        }
        $valid_biller = $this->vas2nets->getAllBillers(true, false,$biller_id);
        if (!$valid_biller) {
            $response[message] = "$user_id account does not exist or its not an API account";
            $user = new UserLib($this->environment);
            $user->setUserId($user_id);
            $user_details = $user->getDetails();
            if(is_array($user_details) && USER_CATEGORY_USER == $user_details["category"]){
                $user_biller_exist = $this->getUserBillers($user_id, $biller_id);
                $foobar = array(COL_USER_ID => $user_id, COL_BILLER_ID => $biller_id);
                if ($user_biller_exist) {
                    $action = "update";
                    $result = $this->userBillerTable->update($foobar, $update);
                } else {
                    $action = "create";
                    $result = $this->userBillerTable->create(array_merge($foobar, $update));
                }
                $response = array(status => STATUS_OK, message => "User biller has been {$action}d", data => array("action" => $action, "result" => $result));
            }
        }
        return $response;
    }
}