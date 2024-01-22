<?php

class Log extends MY_Controller
{
    private $environment = ENV_PROD;
    private $session;

    function __construct()
    {
        parent::__construct();
        $headers = $this->getRequestHeaders(array("PORTAL_AUTH", "USER_AUTH"));
        $this->session = new PortalSession($this->environment);
        $this->session->checkSession($headers["PORTAL_AUTH"], USER_CATEGORY_USER, $headers["USER_AUTH"]);
    }

    function sms(){
        $payload = $this->getGetParams(array("page", "pageSize"));
        $start_date = isset($payload["startDate"]) ? $payload["startDate"] : false;
        $end_date = isset($payload["endDate"]) ? $payload["endDate"] : false;
        $search = isset($payload["search"]) ? $payload["search"] : false;
        $filter = isset($payload["filter"]) ? $payload["filter"] : false;

        $selectable = array(COL_REQUEST_ID, COL_SENDER_ID, COL_RECEIVER, COL_MESSAGE, COL_PAGES, COL_STATUS, COL_BATCH_ID);
        $filterable = array(COL_STATUS => array("pending", "delivered", "deliveryFailed"), COL_BATCH_ID => FILTER_API_DRIVEN);

        $fetcher = new LogFetcher($this->environment, TABLE_SMS);
        $logs = $fetcher->fetch($selectable, array(COL_USER_ID => $this->session->userId), $payload["page"], $payload["pageSize"], $filter, $filterable, $search, $start_date, $end_date);
        $this->respond(array(status => STATUS_OK, data => $logs));
    }

    function email(){
        $payload = $this->getGetParams(array("page", "pageSize"));
        $start_date = isset($payload["startDate"]) ? $payload["startDate"] : false;
        $end_date = isset($payload["endDate"]) ? $payload["endDate"] : false;
        $search = isset($payload["search"]) ? $payload["search"] : false;
        $filter = isset($payload["filter"]) ? $payload["filter"] : false;

        $selectable = array(COL_SENDER_EMAIL, COL_REQUEST_ID, COL_SUBJECT, COL_STATUS);
        $filterable = array(COL_STATUS => array("Successful", "Failed"));

        $fetcher = new LogFetcher($this->environment, TABLE_EMAIL);
        $wallet_history = $fetcher->fetch($selectable, array(COL_USER_ID => $this->session->userId), $payload["page"], $payload["pageSize"], $filter, $filterable, $search, $start_date, $end_date);
        $this->respond(array(status => STATUS_OK, data => $wallet_history));
    }

    function b2b(){

    }

    function smsBreakdown(){
        $payload = $this->getGetParams(array("startDate", "endDate"));
        $start_date = $payload["startDate"];
        $end_date = $payload["endDate"];
        $where_clause = "date(date_created) >= '$start_date' and date(date_created) <= '$end_date' and user_id = '{$this->session->userId}'";

        $table = new Table(TABLE_SMS, $this->environment);
        $breakdown = $table->read(array("select" => "sum(pages) count, network, status",
            "group_by" => "network, status", "where" => $where_clause));
        $breakdown_template = array("pending" => 0, "sent" => 0, "delivered" => 0, "deliveryFailed" => 0);
        $data = array("mtn" => $breakdown_template, "airtel" => $breakdown_template, "glo" => $breakdown_template, "9mobile" => $breakdown_template);

        foreach ($breakdown as $report){
            $count = $report["count"];
            $network = $report["network"];
            $status = $report["status"];
            if($network == ""){
                $possible_network = array_keys($data);
                $network = $possible_network[array_rand($possible_network)];
            }
            if(!isset($data[$network])){
                $data[$network] = $breakdown;
            }
            $data[$network][$status] += $count;
        }
        $this->respond(array(status => STATUS_OK, data => $data));

    }

    function dailyLogCount(){
        $payload = $this->getGetParams(array("startDate", "endDate"));
        $start_date = $payload["startDate"];
        $end_date = $payload["endDate"];
        $where_clause = "date(date_created) >= '$start_date' and date(date_created) <= '$end_date'  and user_id = '{$this->session->userId}'";

        $sms = new Table(TABLE_SMS, $this->environment);
        $email = new Table(TABLE_EMAIL, $this->environment);
        $sms_report = $sms->read(array("select" => "count(user_id) aggregate, date(date_created) day",
            "group_by" => "date(date_created)", "order_by" => "date(date_created) desc", "where" => $where_clause));
        $email_report = $email->read(array("select" => "count(user_id) aggregate, date(date_created) day",
            "group_by" => "date(date_created)", "order_by" => "date(date_created) desc", "where" => $where_clause));

        $this->respond(array(status => STATUS_OK, data => array("sms" => $sms_report, "email" => $email_report, "b2b" => array())));

    }

}
