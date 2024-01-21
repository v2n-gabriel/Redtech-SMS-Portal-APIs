<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author GABRIEL
 */
class UserLib
{

    private $ciInstance;
    private $userTable;
    protected $environment;


    var $userId, $userCategory, $commission, $billerDescription, $vendor;//i ought to create a separate Vendor entity but it seems unnecessary. I am a gen-z, lol
    private $walletRepository, $commissionRepository;

    function __construct($environment = ENV_DEV)
    {
        $this->environment = $environment;
        $this->ciInstance = &get_instance();
        $this->ciInstance->load->model("Table");
        $this->userTable = new Table(TABLE_USER, $environment);
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {//this is strictly meant for admin uses, primarily for transaction reversals
        $this->userId = $user_id;
    }


    public function authenticate($check_active_status = true)
    {//this is purely for API users
        $username = $this->ciInstance->input->server("PHP_AUTH_USER");
        $password = $this->ciInstance->input->server("PHP_AUTH_PW");
        $ip_address = $this->ciInstance->input->ip_address();
        if("35.177.178.82" === $ip_address){
//            die("authenticating.....");
        }
        $where_clause = array(COL_API_USERNAME => $username, COL_API_PASSWORD => $password,
            COL_CATEGORY => CATEGORY_USER);
        if(true === $check_active_status){
            $where_clause[COL_STATUS] = STATUS_ACTIVE;
        }

        $condition = array("where" => $where_clause);

        if(true){
//            $condition["like"] = array(COL_IP_ADDRESS, $ip_address);
        }
        $user = $this->userTable->read($condition);

        if (!$user) {
            respond(array(status => STATUS_UNAUTHORIZED, message => "invalid api credentials"));
        } else {
            $this->userId = $user[0][COL_ID];
            $this->userCategory = CATEGORY_USER;
        }
    }

    public function authenticatePortalLogin($username, $password, $ip_address = false){//only PortalAPI will pass an IP here
        $condition = array("where" => array(COL_PORTAL_USERNAME => $username, COL_PORTAL_PASSWORD => $password));
        if(false !== $ip_address){
            $condition = array("where" => array(COL_API_USERNAME => $username, COL_API_PASSWORD => $password, COL_STATUS => STATUS_ACTIVE, COL_CATEGORY => CATEGORY_PORTALAPI),
                //"like" => array(COL_IP_ADDRESS, $ip_address)
            );
        }

        $user = $this->userTable->read($condition);
        if($user){
            $this->userId = $user[0][COL_ID];
            $this->userCategory = $user[0][COL_CATEGORY];
            return true;
        }

        return false;
    }
//
//
    function getDetails()
    {
        $details = $this->userTable->read(array("where" => array(COL_ID => $this->userId)));
        //return $details ? array("name" => $details[0][COL_COMPANY_NAME], "category" => $details[0][COL_CATEGORY], "emailAddress" => $details[0][COL_EMAIL_ADDRESS], "apiUsername" => $details[0][COL_API_USERNAME],"whitelistedIPs" => explode(",",$details[0][COL_IP_ADDRESS]), "portalUsername" => $details[0][COL_PORTAL_USERNAME], status => $details[0][COL_STATUS], "type" => $details[0][COL_TYPE]) : false;
        return $details ? array("name" => $details[0][COL_COMPANY_NAME], "category" => $details[0][COL_CATEGORY], "emailAddress" => $details[0][COL_EMAIL_ADDRESS], "apiUsername" => $details[0][COL_API_USERNAME], "portalUsername" => $details[0][COL_PORTAL_USERNAME], status => $details[0][COL_STATUS], "type" => $details[0][COL_TYPE]) : false;

    }



    function getUserInfo(){//this is for both client-facing API and portal API
        $balances = $this->getBalance();
        $details = $this->getDetails();
        $profile_info = array("id" => $this->userId, "name" => $details["name"], "category" => $details["category"], "emailAddress" => $details["emailAddress"], status => $details[status], "canAccessAPI" => !is_null($details["apiUsername"]), "canAccessReconciliationPortal" => !is_null($details["portalUsername"]), "type" => $details["type"]);
        return array("profile" => $profile_info, "balance" => $balances);
    }

    function createWallet(){
        if(true == $this->walletRepository->create(array(COL_USER_ID => $this->userId, COL_WALLET_BALANCE => 0))){
            $this->commissionRepository->create(array(COL_USER_ID => $this->userId, COL_WALLET_BALANCE => 0));
            return true;
        }
        return false;
    }
//
//    function whitelistIp($ip_address, $action){
//        $user_account = $this->userTable->read(array("select" => COL_USER_IP_ADDRESS, "where" => "ID = '{$this->userId}'"));
//        if (!$user_account) {
//            respond(array(status => STATUS_FAILED_DEPENDENCY, message => "Account does not exist or account is not an API account"));
//        }
//
//        $existing_ip_addresses = explode(",", $user_account[0][COL_USER_IP_ADDRESS]);
//
//        if ("add" === $action) {
//            $sql = "update {table} set ip_address = ip_address || ',$ip_address' where id = '{$this->userId}'";
//            if(count($existing_ip_addresses) < 2 && empty($existing_ip_addresses[0])){
//                $sql = "update {table} set ip_address = '$ip_address' where id = '{$this->userId}'";
//            }
//            $this->userTable->executeTableQuery($sql);
//            respond(array(status => STATUS_OK, message => "$ip_address has been added."));
//        } elseif ("remove" === $action) {
//            foreach ($existing_ip_addresses as $key => $whitelisted_ip) {
//                if ($ip_address === $whitelisted_ip) {
//                    unset($existing_ip_addresses[$key]);
//                    break;
//                }
//            }
//            $ip_addresses = implode(",", $existing_ip_addresses);
//            $this->userTable->executeTableQuery("update {table} set ip_address = '$ip_addresses' where id = '{$this->userId}'");
//            respond(array(status => STATUS_OK, message => "$ip_address has been removed."));
//
//        } else {
//            respond(array(status => STATUS_FAILED_DEPENDENCY, message => "action param can either be add or remove"));
//        }
//    }
//
    function verifyIfUserHaveAccessToBiller($biller_id, $biller_category)
    {
        $user_biller = $this->userBiller->getUserBillers($this->userId, $biller_id, true);
        if (!isset($user_biller[$biller_id]) || STATUS_ACTIVE !== $user_biller[$biller_id][status]) {
            return "This biller is not available for your account";
        }
        $this->commission = $user_biller[$biller_id]["commission"];
        $this->billerDescription = $user_biller[$biller_id]["info"];
        $vendor_query = $this->userBiller->getVendor($biller_id);
        $this->vendor = $vendor_query["vendor"];
        if ($biller_category !== $vendor_query["category"]) {
            return "$biller_id does not belong to $biller_category category";
        }
        return true;
    }
//
    function getBalance()
    {
//        $commission = $this->commissionRepository->read(array("where" => array(COL_USER_ID => $this->userId)));
//        $wallet = $this->walletRepository->read(array("where" => array(COL_USER_ID => $this->userId)));
        return array("wallet" => 0, "commission" => 0 );
    }

    private function performMonetaryOperation($operation, $operation_type, $current_balance, $amount, $transaction_request_id, $transaction_reference_id, $description, $category)
    {
        if ("wallet" === $category) {
            $valid_operation_types = $this->validWalletOperations;
            $history_repo_source = TABLE_WALLET_HISTORY;
            $destination = $this->walletRepository;
        } elseif ("commission" === $category) {
            $valid_operation_types = $this->validCommissionOperations;
            $history_repo_source = TABLE_COMMISSION_HISTORY;
            $destination = $this->commissionRepository;
        } else {
            return false;
        }

        if ($this->userId && in_array($operation, array(WALLET_OP_DEBIT, WALLET_OP_CREDIT)) && in_array($operation_type, $valid_operation_types)) {
            $arithmetic_operator = WALLET_OP_CREDIT === $operation ? "+" : "-";//this condition was design to avoid gbese. Its better to refund a customer that to be dragging them to pay us our money
            $operation_execution_sql = "update {table} set balance = balance {$arithmetic_operator} $amount where " . COL_USER_ID . " = '{$this->userId}'";
            $operation_execution = $destination->executeTableQuery($operation_execution_sql);
            if (true == $operation_execution) {
                $history = new Table($history_repo_source, $this->environment);
                $history->create(array(COL_BALANCE_BEFORE => $current_balance, COL_AMOUNT => $amount, COL_DESCRIPTION => $description,
                    COL_TRANSACTION_REQUEST_ID => $transaction_request_id, COL_TRANSACTION_REFERENCE_ID => $transaction_reference_id,
                    COL_OPERATION => $operation, COL_TYPE => $operation_type, COL_USER_ID => $this->userId));
                return true;
            }
        }

        return false;
    }

    function performOperationOnWallet($operation, $operation_type, $current_balance, $amount, $transaction_request_id, $transaction_reference_id, $description)
    {
        return $this->performMonetaryOperation($operation, $operation_type, $current_balance, $amount, $transaction_request_id, $transaction_reference_id, $description, "wallet");
    }

    function performOperationOnCommission($operation, $operation_type, $current_balance, $amount, $transaction_request_id, $transaction_reference_id, $description)
    {
        return $this->performMonetaryOperation($operation, $operation_type, $current_balance, $amount, $transaction_request_id, $transaction_reference_id, $description, "commission");

    }


    function reverseTransaction($amount, $commission, $transaction_request_id, $transaction_reference_id, $description){
        //this ought to be an all-in-one transaction operation i.e. either all pass or all failed
        //but sadly, the application design is not transaction oriented. Lets hope that this operation wont break in the middle

        //todo: get the balance and log it appropriately
        $wallet_credit = $this->performOperationOnWallet(WALLET_OP_CREDIT, WALLET_OP_TYPE_REVERSAL_CREDIT, "", $amount, $transaction_request_id, $transaction_reference_id, $description);
        if(true == $wallet_credit){
            return $this->performOperationOnCommission(WALLET_OP_DEBIT, COMMISSION_OP_TYPE_REVERSAL_DEBIT, "", $commission, $transaction_request_id, $transaction_reference_id, $description);

        }
        return false;
    }


}
