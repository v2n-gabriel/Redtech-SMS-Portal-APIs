<?php

class Profile extends MY_Controller
{
    private $environment = ENV_DEV;
    private $session, $user;
    function __construct()
    {
        parent::__construct();
        $headers = $this->getRequestHeaders(array("PORTAL_AUTH", "USER_AUTH"));
        $this->session = new PortalSession($this->environment);
        $this->session->checkSession($headers["PORTAL_AUTH"],USER_CATEGORY_USER, $headers["USER_AUTH"]);
        $this->user = new UserLib($this->environment);
        $this->user->setUserId($this->session->userId);
    }

    function getDetails(){//return the profile details
        $this->respond(array(status => STATUS_OK, data => $this->user->getUserInfo()));
    }
}