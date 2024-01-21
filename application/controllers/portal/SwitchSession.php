<?php

class SwitchSession extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    function switchIt($environment = ENV_DEV){
        $current_position = "v1" == $environment ? "live" : "dev";
        $destination = "dev" == $current_position ?  "live": "dev";
        $headers = $this->getRequestHeaders(array ("PORTAL_AUTH", "USER_AUTH"));
        $portal_auth = $headers["PORTAL_AUTH"];
        $user_auth = $headers["USER_AUTH"];

        $portal_session = new PortalSession($current_position);
        $portal_session->checkSession($portal_auth, array(CATEGORY_ADMIN, CATEGORY_USER, CATEGORY_SUPERADMIN), $user_auth);
        $portal_session->switchUserSessionAcrossEnvironment($user_auth, $current_position, $destination);

        $destination_session = new PortalSession($destination);
        $new_portal_auth = $destination_session->getUserActiveSession($portal_session->portalAPIId);
        if(false == $new_portal_auth){
            $new_portal_auth = $portal_session->portalAPIId. self::generateSessionId(15).self::generateSessionId(17).self::generateSessionId(18).self::generateSessionId(10);
            $expiration_window = $destination_session->createSession($portal_session->portalAPIId, USER_CATEGORY_PORTALAPI, $new_portal_auth);
        }

        $this->respond(array(status => STATUS_OK, data => array(status => STATUS_OK, "userId" => $portal_session->portalAPIId, "userCategory" => USER_CATEGORY_PORTALAPI, "sessionAuth" => $new_portal_auth)));


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

}