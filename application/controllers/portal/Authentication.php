<?php

class Authentication extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library("PortalSession");
    }

    function login($environment){
        if("v1" === $environment){
            $environment = ENV_PROD;
        }
        $user = new UserLib($environment);
        $session = new PortalSession($environment);

        if("get" == $this->input->method()){//portal is trying to get auth
            $login_successful = $user->authenticatePortalLogin($this->input->server("PHP_AUTH_USER"), $this->input->server("PHP_AUTH_PW"), $this->input->ip_address());
        }else{
            //users are trying to login
            $headers = $this->getRequestHeaders(array("PORTAL_AUTH"));

            $session->checkSession($headers["PORTAL_AUTH"]);
            $payload = $this->getBodyParams(array("username", "password"));
            $login_successful = $user->authenticatePortalLogin($payload["username"], $payload["password"]);
        }

        if(false === $login_successful){
            if("get" == $this->input->method()){
                $this->respond(array(status => STATUS_FAILED_DEPENDENCY, message => "Invalid PortalApi credentials"));
            }else{
                $this->respond(array(status => STATUS_FAILED_DEPENDENCY, message => "Wrong Username or Password"));
            }
        }else{
            $session_id = $user->userId. self::generateSessionId(15).self::generateSessionId(17).self::generateSessionId(18).self::generateSessionId(10);
            $expiration_window = $session->createSession($user->userId, $user->userCategory, $session_id);
            $this->respond(array(status => STATUS_OK, data => array(status => STATUS_OK, "userId" => $user->userId, "userCategory" => $user->userCategory, "sessionAuth" => $session_id, "expirationWindow" => "{$expiration_window}minutes")));
        }

    }

    private static function generateSessionId($length){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function logout($environment){
        if("v1" === $environment){
            $environment = ENV_PROD;
        }
        $headers = $this->getRequestHeaders(array("USER_AUTH"));
        $user_auth = $headers["USER_AUTH"];
        $session = new PortalSession($environment);
        $session->endSession($user_auth);
        $this->respond(array(status => STATUS_OK, message => "User has been logged out"));
    }

    function getPortalActiveSession($environment){
        if("v1" === $environment){
            $environment = ENV_PROD;
        }
        $user = new UserLib($environment);
        $session = new PortalSession($environment);
        $login_successful = $user->authenticatePortalLogin($this->input->server("PHP_AUTH_USER"), $this->input->server("PHP_AUTH_PW"), $this->input->ip_address());

        if(CATEGORY_PORTALAPI != $user->userCategory){
            $this->respond(array(status => STATUS_FAILED_DEPENDENCY, message => "This operation is only available for ".CATEGORY_PORTALAPI . " users"));
        }

        if(false === $login_successful){
                $this->respond(array(status => STATUS_FAILED_DEPENDENCY, message => "Invalid PortalApi credentials"));
        }else{
            $active_session = $session->getUserActiveSession($user->userId);
            if(false == $active_session){
                $this->respond(array(status => STATUS_FAILED_DEPENDENCY, message => "You do not have an active session. Please login to generate a session"));
            }
            $this->respond(array(status => STATUS_OK, message => "Request has been processed successfully", data => array("sessionId" => $active_session)));
        }
    }

}