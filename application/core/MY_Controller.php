<?php

/**
 * Created by PhpStorm.
 * User: GABRIEL
 * Date: 18/03/2019
 * Time: 11:48
 */
class MY_Controller extends CI_Controller {


    private static $contentType;
    private static $expectedRequestHeaders = array(), $requiredParamKeys = array();
    private $service;
    protected $logDirectory;

    protected $maximumPayloadLength = 1000;

    function __construct($log_directory = "", $content_type = null) {
        parent::__construct();
        self::$contentType = $content_type == null ? "application/json" : $content_type;
        $this->service = ucwords($this->router->class);
        $this->logDirectory = $log_directory;
    }


    protected function setRequiredParamKeys($keys = array()) {
        self::$requiredParamKeys = $keys;
    }

    protected function respond($response_message = array()) {
        respond($response_message);
    }

    private function unauthorized($response = array()) {
        $response [status] = STATUS_UNAUTHORIZED;
        $response[description] = "access denied";

        self::respond($response); #to set the header
        header("Content-type:application/json");
        die(json_encode($response));
    }

    protected function restrictToIps($ips = array()) {
        if (!in_array($this->input->ip_address(), $ips)) {
            //self::_unauthorized("We will track you, catch you and jail you for this. Just watch out!!!");
            self::unauthorized(array(message => "access denied. Unrecognised ip address"));
        }
    }

    protected function bindServiceToContainer($class_name, $type = "library"){
        $this->load->$type("vendor/$class_name");
    }

    protected function getRequestHeaders($expected_headers = array()) {
        $request_headers = $this->input->request_headers();

        foreach ($expected_headers as $key) {

            if (!isset($request_headers[$key])) {
                self::unauthorized(array(
                    message => "Incorrect or Incomplete Headers",
                    extra => "$key is missing"));
            }
        }
        return $request_headers;
    }

    protected function getBodyParams($keys = array()) {
        self::setRequiredParamKeys($keys);
        $request_body = (array) json_decode($this->input->raw_input_stream);
        logString("request payload is " . json_encode($request_body));

        foreach (self::$requiredParamKeys as $param_key) {
            if (!isset($request_body[$param_key])) {
                logString("$param_key not found in payload");
                self::unauthorized(array(
                    message => "Incomplete Body Parameters",
                    extra => "$param_key is missing"
                ));
            }
        }
        return $request_body;
    }

    protected function getGetParams($keys = array()) {
        self::setRequiredParamKeys($keys);
        $get_params = $this->input->get();
        logString("request payload is " . json_encode($get_params));

        foreach (self::$requiredParamKeys as $param_key) {
            if (!isset($get_params[$param_key])) {
                self::unauthorized(array(
                    message => "Incomplete Get Parameters",
                    extra => "$param_key is missing"
                ));
            }
        }
        return $get_params;
    }
    
    protected function getPostParams($keys = array()) {
        self::setRequiredParamKeys($keys);
        $post_params = $this->input->post();
        logString("request payload is " . json_encode($post_params));

        foreach (self::$requiredParamKeys as $param_key) {
            if (!isset($post_params[$param_key])) {
                self::unauthorized(array(
                    message => "Incomplete Post Parameters",
                    extra => "$param_key is missing"
                ));
            }
        }
        return $post_params;
    }

    protected function log($details = null) {
        $new_line = "\n";
        if (is_array($details)) {
            $string = $new_line = "";
            foreach ($details as $key => $value) {
                $value = is_array($value) | is_object($value) ? json_encode($value) : $value;
                $string .= "\n$key => $value";
            }
            $details = $string;
        }
        $ip = $this->input->ip_address();
        log_message('debug', "\nService => $this->service \nip => $ip $new_line $details\n\n\t");
    }

    function logStringToFile($string, $sub_folder = false) {
        $log_path = false === $sub_folder ? $this->logDirectory : $this->logDirectory ."/". $sub_folder;
        $today_date = date("Y-m-d");
        $timestamp = date("Y-m-d H:i:s.u");
        $log_file = $this->config->item("log_path"). "$log_path/log-$today_date.log";
        file_put_contents($log_file, "$timestamp ==> $string\n\n", FILE_APPEND);
    }

}
