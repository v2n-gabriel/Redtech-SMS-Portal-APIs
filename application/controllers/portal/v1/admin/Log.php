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
        $this->session->checkSession($headers["PORTAL_AUTH"], USER_CATEGORY_ADMIN, $headers["USER_AUTH"]);
    }

    function sms(){
        $payload = $this->getGetParams(array("page", "pageSize"));
        $start_date = isset($payload["startDate"]) ? $payload["startDate"] : false;
        $end_date = isset($payload["endDate"]) ? $payload["endDate"] : false;
        $search = isset($payload["search"]) ? $payload["search"] : false;
        $filter = isset($payload["filter"]) ? $payload["filter"] : false;

        $selectable = array(COL_USER_ID, COL_REQUEST_ID, COL_SENDER_ID, COL_RECEIVER, COL_MESSAGE, COL_PAGES, COL_STATUS);
        $filterable = array(COL_STATUS => array("pending", "sent", "delivered", "deliveryFailed"), COL_USER_ID => FILTER_API_DRIVEN);

        $fetcher = new LogFetcher($this->environment, TABLE_SMS);
        $logs = $fetcher->fetch($selectable, array(), $payload["page"], $payload["pageSize"], $filter, $filterable, $search, $start_date, $end_date);
        $this->respond(array(status => STATUS_OK, data => $logs));
    }

    function email(){
        $payload = $this->getGetParams(array("page", "pageSize"));
        $start_date = isset($payload["startDate"]) ? $payload["startDate"] : false;
        $end_date = isset($payload["endDate"]) ? $payload["endDate"] : false;
        $search = isset($payload["search"]) ? $payload["search"] : false;
        $filter = isset($payload["filter"]) ? $payload["filter"] : false;

        $selectable = array(COL_USER_ID, COL_SENDER_EMAIL, COL_REQUEST_ID, COL_SUBJECT, COL_STATUS);
        $filterable = array(COL_STATUS => array("Successful", "Failed"), COL_USER_ID => FILTER_API_DRIVEN);

        $fetcher = new LogFetcher($this->environment, TABLE_EMAIL);
        $wallet_history = $fetcher->fetch($selectable, array(), $payload["page"], $payload["pageSize"], $filter, $filterable, $search, $start_date, $end_date);
        $this->respond(array(status => STATUS_OK, data => $wallet_history));
    }

    function b2b(){

    }

}
