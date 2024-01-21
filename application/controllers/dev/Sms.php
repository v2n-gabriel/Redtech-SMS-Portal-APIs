<?php

class Sms extends MY_Controller
{
    private $environment = ENV_DEV;
    private $user, $logPath;
    function __construct()
    {
        parent::__construct("{$this->environment}/sms");
        $this->user = new UserLib($this->environment);
//        $body = $this->getBodyParams();
//        log_message("debug", json_encode($body));
        $this->user->authenticate();
    }

    function index(){
        $payload = $this->validateRequestPayload();
        $sms_repository = new Table(TABLE_SMS, $this->environment);
        $inserted = $sms_repository->create($payload, true);
        $this->respond(array(status => STATUS_OK, "accepted" => $inserted, "rejected" => count($payload) - $inserted, message => "Request has been processed successfully"));
    }

    private function validateRequestPayload() {
        $payload = $this->getBodyParams(array("sms"));
        $request = $payload["sms"];
        $this->logMessage("request::{$this->user->userId} ==>" . json_encode($request));
        $good_payload = true;
        $message = "";
        if (!is_array($request)) {
            $good_payload = false;
            $message = "sms must be a list of objects";
        } elseif (1 > count($request)) {
            $good_payload = false;
            $message = "sms must contain at least an object";
        }elseif (count($request) > $this->maximumPayloadLength) {
            $good_payload = false;
            $message = "maximum payload limit exceeded. Limit is {$this->maximumPayloadLength}";
        } else {
            $needed_params = array();
            $this->load->library("SmsPageCalculator");
            foreach ($request as $object) {
                if (!is_object($object)) {
                    $message = "one or more member of the list is not an object";
                    $good_payload = false;
                    break;
                } elseif (count(array_diff(array("id", "sender", "receiver", "message"), array_keys(get_object_vars($object)))) > 0) {
                    $message = "one or more member of the list is not properly formed";
                    $good_payload = false;
                    break;
                } else {
                    $needed_params[] = array(
                        COL_UNIQUE_ID => $this->user->userId . "_" . $object->id, COL_REQUEST_ID => $object->id, COL_USER_ID => $this->user->userId,
                        COL_SENDER_ID => $object->sender, COL_RECEIVER => $object->receiver, COL_MESSAGE => $object->message, COL_PAGES => SmsPageCalculator::calculatePages($object->message)
                    );
                }
            }
            $request = $needed_params;
        }

        if ($good_payload) {
            return $request;
        } else {
            $response = array(status => STATUS_FAILED_DEPENDENCY, message => $message);
            $this->logMessage("response::{$this->user->userId} ==> ".json_encode($response));
            $this->respond($response);
        }
    }

    private function logMessage($string)
    {
        $this->logPath ? $this->logStringToFile($string, $this->logPath) : $this->logStringToFile($string);
    }

}