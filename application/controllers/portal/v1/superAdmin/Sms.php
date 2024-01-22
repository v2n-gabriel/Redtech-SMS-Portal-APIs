<?php

class Sms extends MY_Controller
{
    private $environment = ENV_PROD;
    private $session, $user;
    private $batchRepo, $smsRepo;


    function __construct()
    {
        parent::__construct();
        $headers = $this->getRequestHeaders(array("PORTAL_AUTH", "USER_AUTH"));
        $this->session = new PortalSession($this->environment);
        $this->session->checkSession($headers["PORTAL_AUTH"],USER_CATEGORY_SUPERADMIN, $headers["USER_AUTH"]);
        $this->user = new UserLib($this->environment);
        $this->user->setUserId($this->session->userId);
        $this->batchRepo = new Table(TABLE_BATCH, $this->environment);
        $this->smsRepo = new Table(TABLE_SMS, $this->environment);
    }


    function send(){
        $payload = $this->getBodyParams(array("batchId", "title", "sender", "message", "receivers"));
        $receivers = $payload["receivers"];
        $response = array(status => STATUS_FAILED_DEPENDENCY, message => "receivers should be a list of phone numbers");
        if (is_array($receivers) && 0 < count($receivers)){
            $batch_id = $payload["batchId"];
            $batch_check = $this->batchRepo->read(array("where" => array(COL_UNIQUE_ID => $batch_id . $this->session->userId)));
            $response["message"] = "This batch has already been processed";
            if(!$batch_check){
                $this->load->library("SmsPageCalculator");
                $message = $payload["message"];
                $sender_id = $payload["sender"];
                $unique_id = $batch_id . $this->session->userId;
                $batch_info = array(COL_UNIQUE_ID => $unique_id, COL_USER_ID => $this->session->userId,
                    COL_BATCH_ID => $batch_id, COL_TITLE => $payload["title"], COL_SENDER_ID => $sender_id,
                    COL_MESSAGE => $message, COL_SCHEDULED_DATE => date("d-m-Y G:i:s"));
                $sms_info = array();
                foreach ($receivers as $receiver){
                    $sms_info[] = array(COL_UNIQUE_ID => $unique_id.$receiver.rand(12,998876),
                        COL_REQUEST_ID => $unique_id.$receiver, COL_USER_ID => $this->session->userId,
                        COL_SENDER_ID => $sender_id, COL_RECEIVER => $receiver, COL_MESSAGE => $message,
                        COL_PAGES => SmsPageCalculator::calculatePages($message), COL_BATCH_ID => $batch_id);
                }
                $this->batchRepo->create($batch_info);
                $this->smsRepo->create($sms_info, true);
                $response = array(status => STATUS_OK, message  => "Messages has been sent successfully");
            }
        }
        $this->respond($response);
    }

    function save(){
        $payload = $this->getBodyParams(array("batchId", "title", "sender", "message", "receivers", "dateTime"));
        $receivers = $payload["receivers"];
        $response = array(status => STATUS_FAILED_DEPENDENCY, message => "receivers should be a list of phone numbers");
        if (is_array($receivers) && 0 < count($receivers)) {
            $time = $payload["dateTime"];
            $response[message] = "Please select a valid datetime";
            if (DateTime::createFromFormat("d-m-Y G:i:s", $time) !== FALSE && strtotime($time) > strtotime(date("d-m-Y G:i:s"))) {
                $batch_id = $payload["batchId"];
                $batch_check = $this->batchRepo->read(array("where" => array(COL_UNIQUE_ID => $batch_id . $this->session->userId)));
                $response[message] = "This batch has already been processed";
                if (!$batch_check) {
                    $this->load->library("SmsPageCalculator");
                    $message = $payload["message"];
                    $sender_id = $payload["sender"];
                    $unique_id = $batch_id . $this->session->userId;
                    $batch_info = array(COL_UNIQUE_ID => $unique_id, COL_USER_ID => $this->session->userId,
                        COL_BATCH_ID => $batch_id, COL_TITLE => $payload["title"], COL_SENDER_ID => $sender_id,
                        COL_MESSAGE => $message, COL_SCHEDULED_DATE => $time, COL_STATUS => "scheduled");

                    $sms_info = array();
                    foreach ($receivers as $receiver) {
                        $sms_info[] = array(COL_UNIQUE_ID => $unique_id . $receiver . rand(12, 998876),
                            COL_REQUEST_ID => $unique_id . $receiver, COL_USER_ID => $this->session->userId,
                            COL_SENDER_ID => $sender_id, COL_RECEIVER => $receiver, COL_MESSAGE => $message,
                            COL_PAGES => SmsPageCalculator::calculatePages($message), COL_BATCH_ID => $batch_id,
                            COL_STATUS => "scheduled");
                    }
                    $this->batchRepo->create($batch_info);
                    $this->smsRepo->create($sms_info, true);
                    $response = array(status => STATUS_OK, message => "Messages has been scheduled successfully");
                }
            }
        }
        $this->respond($response);
    }

    function getBatches(){
        $payload = $this->getGetParams(array("page", "pageSize"));
        $start_date = isset($payload["startDate"]) ? $payload["startDate"] : false;
        $end_date = isset($payload["endDate"]) ? $payload["endDate"] : false;
        $search = isset($payload["search"]) ? $payload["search"] : false;
        $filter = isset($payload["filter"]) ? $payload["filter"] : false;

        $selectable = array(COL_BATCH_ID, COL_USER_ID, COL_TITLE, COL_SENDER_ID, COL_STATUS, COL_MESSAGE, COL_SCHEDULED_DATE);
        $filterable = array(COL_STATUS => array("sent", "scheduled"), COL_USER_ID => FILTER_API_DRIVEN);

        $fetcher = new LogFetcher($this->environment, TABLE_BATCH);
        $batch_history = $fetcher->fetch($selectable, array(), $payload["page"], $payload["pageSize"], $filter, $filterable, $search, $start_date, $end_date);
        $this->respond(array(status => STATUS_OK, data => $batch_history));
    }
}