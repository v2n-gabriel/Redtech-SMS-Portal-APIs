<?php

class Disco extends MY_Controller
{
    const DEFAULT_EMAIL_ADDRESS = "gabrieloyetunde@gmail.com", DEFAULT_PHONE_NUMBER = "08132586075";
    private $user, $transaction;
    private $environment = ENV_DEV;
    private $billerCategory = BILLER_CATEGORY_DISCO;
    private $logPath;

    function __construct()
    {
        parent::__construct("$this->environment/$this->billerCategory");
        $this->user = new UserLib($this->environment);
        $this->user->authenticate();
        $this->transaction = new VAS2NetsB2BPlatform($this->environment);
    }

    function validate()
    {
        $this->logPath = "validation";
        $payload = $this->getBodyParams(array("billerId", "customerId", "requestId"));
        $request_id = $payload["requestId"];
        $this->logMesssage("Request::{$this->user->userId}::$request_id => " . json_encode($payload));
        $biller_id = $payload["billerId"];
        $customer_id = $payload["customerId"];
        $user_biller_verification = $this->user->verifyIfUserHaveAccessToBiller($biller_id, $this->billerCategory);
        $response = array(status => STATUS_UNAUTHORIZED, message => $user_biller_verification);
        if (true === $user_biller_verification) {
            $vendor_library = $this->user->vendor;
            $this->logMesssage("Request::{$this->user->userId}::$request_id => $vendor_library");
            $this->bindServiceToContainer($vendor_library);
            $vendor = new $vendor_library($this->environment, $this->billerCategory);
            $validation_response = $vendor->validate($this->user->userId, $biller_id, $customer_id, $request_id);
            $response = array(status => STATUS_OK, data => $validation_response);
        }
        $this->logMesssage("Response::{$this->user->userId}::$request_id => " . json_encode($response));
        $this->respond($response);
    }

    private function logMesssage($string)
    {
        $this->logPath ? $this->logStringToFile($string, "/" . $this->logPath) : $this->logStringToFile($string);
    }

}