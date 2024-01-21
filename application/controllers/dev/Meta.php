<?php

class Meta extends MY_Controller
{
    private $user;
    private $environment = ENV_DEV;
    private $vas2netsB2bPlatform;

    function __construct()
    {
        parent::__construct("user");
        $this->user = new UserLib($this->environment);
        $this->vas2netsB2bPlatform = new VAS2NetsB2BPlatform($this->environment);
    }

    private function performAuthentication($check_active_status = true){
        $this->user->authenticate($check_active_status);
    }

    function getDetails()
    {
        $this->performAuthentication(false);
        $this->respond(array(status => STATUS_OK, data => $this->user->getUserInfo()));
    }

    function getAllBillerCategories(){
        $this->performAuthentication();
        $this->respond(array(status => STATUS_OK, data => array("categories" => $this->vas2netsB2bPlatform->getBillerCategories())));

    }

    function getAllBillers()
    {
        $this->performAuthentication();
        $payload = $this->getGetParams();
        $category = isset($payload["category"]) ? $payload["category"] : false;
        $this->logStringToFile("{$this->user->userId} || {$this->environment} wants to get all billers");
        $this->respond(array(status => STATUS_OK, data => $this->vas2netsB2bPlatform->getAllBillers(false, $category)));
    }

    function getMyBillers()
    {
        $this->performAuthentication();
        $this->logStringToFile( "{$this->user->userId} || {$this->environment} wants to get his/her configured billers");
        $user_biller = new UserBillerLib($this->environment);
        $this->respond(array(status => STATUS_OK, data => array("userId" => $this->user->userId, "billers" => array_values($user_biller->getUserBillers($this->user->userId)))));
    }

}