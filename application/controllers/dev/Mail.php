<?php

class Mail extends MY_Controller
{

    private $environment = ENV_DEV;
    private $user, $logPath;

    function __construct()
    {
        parent::__construct("{$this->environment}/mail");
        $this->user = new UserLib($this->environment);
        $this->user->authenticate();
    }


    function index()
    {
        $payload = $this->getBodyParams(array("sender", "receiver", "body", "title"));
        $this->logMessage("request::{$this->user->userId} ==> " . json_encode($payload));
        $sender = $payload["sender"];
        $receiver = $payload["receiver"];
        $copy = isset($payload["copy"]) ? $payload["copy"] : false;
        $bcc = isset($payload["bcc"]) ? $payload["bcc"] : false;
        $body = $payload["body"];
        $title = $payload["title"];
        if (!is_object($sender)) {
            $error_message = "sender should be an object";
        } elseif (!property_exists($sender, "name") || !property_exists($sender, "email")) {
            $error_message = "name or email property is missing in sender object";
        } else {
            $error_message = self::confirmMailList($receiver, "receiver");
            if (false === $error_message && false !== $copy) {
                $error_message = self::confirmMailList($copy, "copy");
            }

            if (false === $error_message && false !== $bcc) {
                $error_message = self::confirmMailList($bcc, "bcc");
            }
        }


        $response = array(status => STATUS_FAILED_DEPENDENCY, message => $error_message);
        if (false === $error_message) {
            $response = array(status => STATUS_OK, data => array(status => "Success", message => "Mail has been sent"));

            if (ENV_PROD === $this->environment) {

                $response = array(status => STATUS_OK, data => array(status => "Failed", message => "Mail could not be sent. Please try again later"));
                $payload = array("subject" => $title, "senderName" => $sender->name, "senderAddress" => $sender->email,
                    "rcList" => array_map(function ($element) {
                        return $element->email;
                    }, $receiver), "content"=> $body);
                if (false !== $copy) {
                    $payload["ccList"] = array_map(function ($element) {
                        return $element->email;
                    }, $copy);
                }
                if (false !== $bcc) {
                    $payload["bccList"] = array_map(function ($element) {
                        return $element->email;
                    }, $bcc);
                }
                $api_payload = json_encode($payload);
                $this->logMessage("request payload is $api_payload");
                $api_response = self::sendEmail($api_payload);
                $this->logMessage("api response is $api_response");
                $decoded_response = json_decode($api_response);
                if(is_object($decoded_response) && property_exists($decoded_response, "status") && "SUCCESSFUL" === $decoded_response->status){
                    $response = array(status => STATUS_OK, data => array(status => "Success", message => "Mail has been sent"));
                }
            }
            $table = new Table(TABLE_EMAIL, $this->environment);
            $table->create(array(COL_USER_ID => $this->user->userId, COL_SENDER_NAME => $sender->name, COL_SENDER_EMAIL => $sender->email, COL_SUBJECT => $title, COL_STATUS => $response[data][status]));
        }

        $this->respond($response);
    }

    private static function sendEmail($payload)
    {
        $endpoint = "http://127.0.0.1:9085/v2nemail/api/v1/email";
        return triggerPostRequest($endpoint, $payload, array("Content-Type: application/json"));

    }

    private static function confirmMailList($list, $key)
    {
        if (!is_array($list)) {
            return "$key should be a list of name value pairs";
        }

        if (1 > count($list)) {
            return "$key is empty";
        }

        foreach ($list as $pair) {
            if (!is_object($pair)) {
                return "An item in $key list is not an object. Kindly adjust your payload";
            }

            if (!property_exists($pair, "name") || !property_exists($pair, "email")) {
                return "name or email property is missing in object";
            }

            if (!filter_var($pair->email, FILTER_VALIDATE_EMAIL)) {
                return "$pair->email is not a valid email address";
            }

            if (is_numeric($pair->name)) {
                return "Please specify the correct name for $pair->email in your $key list";
            }
        }


        return false;
    }

    private function logMessage($string)
    {
        $this->logPath ? $this->logStringToFile($string, $this->logPath) : $this->logStringToFile($string);
    }

}