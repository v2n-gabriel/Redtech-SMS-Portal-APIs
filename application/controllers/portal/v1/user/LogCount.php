<?php

class LogCount extends MY_Controller
{
    protected $environment = ENV_PROD;

    protected $session;

    public function __construct()
    {
        parent::__construct();
        $headers = $this->getRequestHeaders(array("PORTAL_AUTH", "USER_AUTH"));
        $this->session = new PortalSession($this->environment);
        $this->session->checkSession($headers["PORTAL_AUTH"], USER_CATEGORY_USER, $headers["USER_AUTH"]);
    }

    public function index()
    {
        $user_id_clause =  " and ".COL_USER_ID ." = '{$this->session->userId}'";

        $sms = new Table(TABLE_SMS, $this->environment);
        $email = new Table(TABLE_EMAIL, $this->environment);

        $last30 = COL_DATE_CREATED. " >= '". date('Y-m-d', strtotime('30 days ago'))."'";
        $last7  = COL_DATE_CREATED. " >= '".date('Y-m-d', strtotime('7 days ago'))."'";
        $today  = COL_DATE_CREATED. " >= '".date('Y-m-d')."'";
        $select = "count(".COL_USER_ID.") aggregate";

        $smsCount30Days = $sms->read(array("select" => $select, "where" => $last30 . $user_id_clause));
        $smsCount7Days = $sms->read(array("select" => $select, "where" => $last7 . $user_id_clause));
        $smsCountToday = $sms->read(array("select" => $select, "where" => $today . $user_id_clause));

        $emailCount30Days = $email->read(array("select" => $select, "where" => $last30 . $user_id_clause));
        $emailCount7Days = $email->read(array("select" => $select, "where" => $last7 . $user_id_clause));
        $emailCountToday = $email->read(array("select" => $select, "where" => $today . $user_id_clause));

        $data = array (
            '30' => array (
                'sms'   => $smsCount30Days[0]['aggregate'],
                'email' => $emailCount30Days[0]['aggregate'],
                'b2b'   => 0
            ),
            '7'  => array (
                'sms'   => $smsCount7Days[0]['aggregate'],
                'email' => $emailCount7Days[0]['aggregate'],
                'b2b'   => 0
            ),
            '1'  => array (
                'sms'   => $smsCountToday[0]['aggregate'],
                'email' => $emailCountToday[0]['aggregate'],
                'b2b'   => 0
            )
        );

        $this->respond(array (status => STATUS_OK, data => $data));
    }
}