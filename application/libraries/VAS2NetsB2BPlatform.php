<?php

class VAS2NetsB2BPlatform
{
    const TEST_USERNAME = "reDApiBills", TEST_PASSWORD = "geh4y_!1ilhtb@", TEST_BASEURL = "http://127.0.0.1:8036/b2bapi/dev";
    const LIVE_USERNAME = "", LIVE_PASSWORD = "", LIVE_BASEURL = "http://127.0.0.1:8036/b2bapi/v1";

    private $environment;
    private $apiUsername,$apiPassword, $baseUrl;
    private $appInstance;

    function __construct($environment = ENV_DEV)
    {
        $this->environment = $environment;
        $this->appInstance = &get_instance();
        $this->apiUsername = self::TEST_USERNAME;
        $this->apiPassword = self::TEST_PASSWORD;
        $this->baseUrl = self::TEST_BASEURL;
        if(ENV_PROD == $environment){
            $this->apiUsername = self::LIVE_USERNAME;
            $this->apiPassword = self::LIVE_PASSWORD;
            $this->baseUrl = self::LIVE_BASEURL;
        }
    }

    function getBillerCategories(){
        $categories = $this->sendRequest("meta/getBillerCategories");
        return $categories ? $categories->data->categories : array();
    }

    function getAllBillers($is_admin = false, $category = false, $biller_id = false){
        $billers = array();
        $redtech_billers = $this->sendRequest("meta/getMyBillers");
        if($redtech_billers){
            $redtech_billers = $redtech_billers->data->billers;
            foreach ($redtech_billers as $index => $redtech_biller){
                unset($redtech_biller->userId);
                if(false === $is_admin){
                    unset($redtech_biller->commission);
                }
                $redtech_biller->imageUrl = str_replace(array("127.0.0.1:8036", "127.0.0.1"), "132.145.231.191", $redtech_biller->imageUrl);
                $billers[] = $redtech_biller;
            }
        }

        if(false !== $category){
            $billers = array_values(array_filter($billers, function ($item) use ($category){
                return $category === $item->category;
            }));
        }

        if(false !== $biller_id){
            $billers = array_values(array_filter($billers, function ($item) use ($biller_id){
                return $biller_id === $item->id;
            }));
        }


        return $billers;
    }

    function getMyBiller(){

    }

    function fetchBouquet(){

    }

    function runValidation(){

    }

    function makePayment(){

    }

    function performRequery(){

    }


    private function sendRequest($uri, $payload = "", $method = "GET", $log_path = "others"){
        $this->logStringToFile("Request ==> $uri || $method || $payload", $log_path);
        $header = array("Accept: application/json, application/*+json", "Authorization: Basic " . base64_encode($this->apiUsername.":".$this->apiPassword), "Content-Type: application/json");
        if("POST" == $method){
            $response = triggerPostRequest($this->baseUrl."/$uri", $payload, $header);
        }else{
            $response = triggerGetRequest($this->baseUrl."/$uri", $header);
        }
        $this->logStringToFile("Response ==> $uri || $method || $payload || $response");
        $decoded_response = json_decode($response);

        return is_object($decoded_response) && property_exists($decoded_response, "status") && 200 == $decoded_response->status ? $decoded_response : false;
    }

    private function logStringToFile($string, $sub_folder = "others")
    {
        $base_log_path = $this->appInstance->config->item("log_path") . "vas2nets/{$this->environment}";
        $log_path = false === $sub_folder ? $base_log_path : $base_log_path . "/" . $sub_folder;
        $today_date = date("Y-m-d");
        $timestamp = date("Y-m-d H:i:s.u");
        file_put_contents("$log_path/log-$today_date.log", "$timestamp ==> $string\n\n", FILE_APPEND);
    }

}