<?php

class Biller extends MY_Controller
{
    private $environment = ENV_DEV;
    private $session, $vas2nets;
    private $userBiller;

    function __construct()
    {
        parent::__construct();
        $headers = $this->getRequestHeaders(array("PORTAL_AUTH", "USER_AUTH"));
        $this->session = new PortalSession($this->environment);
        $this->session->checkSession($headers["PORTAL_AUTH"], USER_CATEGORY_ADMIN, $headers["USER_AUTH"]);
        $this->vas2nets = new VAS2NetsB2BPlatform($this->environment);
        $this->userBiller = new UserBillerLib($this->environment);
    }

    function category(){
        $this->respond(array(status => STATUS_OK, data => $this->vas2nets->getBillerCategories()));
    }

    function getAll(){
        $param = $this->getGetParams();
        $category = isset($param["category"]) ? $param["category"] : false;
        $biller_id = isset($param["billerId"]) ? $param["billerId"] : false;
        $this->respond(array(status => STATUS_OK, data => $this->vas2nets->getAllBillers(true, $category, $biller_id)));
    }

    function getUserBiller(){
        $payload = $this->getGetParams(array("page", "pageSize"));
        $start_date = isset($payload["startDate"]) ? $payload["startDate"] : false;
        $end_date = isset($payload["endDate"]) ? $payload["endDate"] : false;
        $search = isset($payload["search"]) ? $payload["search"] : false;
        $filter = isset($payload["filter"]) ? $payload["filter"] : false;

        $selectable = array(COL_USER_ID, COL_BILLER_ID, COL_BILLER_CATEGORY, COL_COMMISSION, COL_STATUS);
        $filterable = array(COL_STATUS => array(STATUS_ACTIVE, STATUS_DISABLED), COL_USER_ID => FILTER_API_DRIVEN, COL_BILLER_ID => FILTER_API_DRIVEN, COL_BILLER_CATEGORY => FILTER_API_DRIVEN);

        $fetcher = new LogFetcher($this->environment, TABLE_USER_BILLER);
        $logs = $fetcher->fetch($selectable, array(), $payload["page"], $payload["pageSize"], $filter, $filterable, $search, $start_date, $end_date);
        $this->respond(array(status => STATUS_OK, data => $logs));
    }


    function configure()
    {
        $this->respond($this->userBiller->configureOrUpdateUserBiller($this->getBodyParams(array("billerId", "userId", "commission", "status"))));
    }

}