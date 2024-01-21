<?php

class Profile extends MY_Controller
{
    private $environment = ENV_DEV;
    private $session;
    var $admin;

    function __construct()
    {
        parent::__construct();
        $headers = $this->getRequestHeaders(array("PORTAL_AUTH", "USER_AUTH"));
        $this->session = new PortalSession($this->environment);
        $this->session->checkSession($headers["PORTAL_AUTH"], USER_CATEGORY_ADMIN, $headers["USER_AUTH"]);
        $this->admin = new Admin($this->environment);
    }

    function getDetails(){//return the profile details
        $this->admin->setUserId($this->session->userId);
        $this->respond(array(status => STATUS_OK, data => $this->admin->getUserInfo()));
    }

}