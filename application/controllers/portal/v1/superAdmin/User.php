<?php

class User extends MY_Controller
{
    private $environment = ENV_PROD;

    var $admin;

    function __construct()
    {
        parent::__construct();

        $headers = $this->getRequestHeaders(array("PORTAL_AUTH", "USER_AUTH"));
        $session = new PortalSession($this->environment);
        $session->checkSession($headers["PORTAL_AUTH"], USER_CATEGORY_SUPERADMIN, $headers["USER_AUTH"]);
        $this->admin = new Admin($this->environment);
    }

    function getAllUserCategory()
    {
        $this->respond(array(status => STATUS_OK, data => $this->admin->getAllUserCategory()));
    }

    public function index()
    {
        $params = $this->getGetParams();

        if (isset($params["userId"])) {
            if (! $users = $this->admin->getAllUsers($params["userId"])) {
                $this->respond(array (status => STATUS_FAILED_DEPENDENCY, message => "Invalid user ID"));
            }

            $this->respond(array (status => STATUS_OK, data => $users[0]));
        }

        $this->respond(array (status => STATUS_OK, data => $this->admin->getAllUsers()));
    }

    function create()
    {
        $payload = $this->getBodyParams(array("userCategory"));
        $user_category = $payload["userCategory"];
        $valid_categories = $this->admin->getAllUserCategory();
        $response = array(status => STATUS_FAILED_DEPENDENCY, message => "You can only create any of " . implode(", ", $valid_categories));
        if (in_array($user_category, $valid_categories)) {
            $params = array("userId", "portalUsername", "portalPassword", "emailAddress");
            if (USER_CATEGORY_USER == $user_category) {
                $params = array_merge($params, array("apiUsername", "apiPassword", "companyName", "type"));
                $payload = $this->getBodyParams($params);
                $response = $this->createNormalUser($payload);
            } else {
                $payload = $this->getBodyParams($params);
                $response = $this->createAdmin($payload);
            }
        }
        $this->respond($response);
    }

    function update(){
        $payload = $this->getBodyParams(array("userCategory", "userId"));
        $user_id = $payload["userId"];
        $user_category = $payload["userCategory"];
        $response = array(status => STATUS_FAILED_DEPENDENCY, message => "Invalid user category or user");
        if(in_array($user_category, array(USER_CATEGORY_USER, USER_CATEGORY_ADMIN)) && 1 === count($this->admin->getDetailsSpecial(COL_ID . " = '$user_id'"))){
            $updatable_columns = array("portalUsername", "portalPassword", "status");
            if(USER_CATEGORY_USER == $user_category){
                $updatable_columns = array_merge($updatable_columns, array("apiUsername", "apiPassword", "companyName", "type"));
            }
            $update = array();
            foreach ($payload as $key => $value){
                if(in_array($key, $updatable_columns)){
                    $update[strtolower(camelCaseToSnakeCase($key))] = $value;
                }
            }
            $response[message] = "You did not indicate a valid {$user_category}'s property that can be updated";
            $response["updatableProperties"] = implode(", ", $updatable_columns);
            if($update){
                if(isset($update[COL_STATUS]) && !in_array($update[COL_STATUS], array(STATUS_ACTIVE, STATUS_DISABLED))){
                    $response[message] = "Status can only be set to either Active or Disabled";
                }elseif(isset($update[COL_TYPE]) && !in_array($update[COL_TYPE], array(USER_TYPE_PREPAID, USER_TYPE_POSTPAID))){
                    $response[message] = "Type can only be set to either Prepaid or Postpaid";
                }else{
                    $response[message] = "oooppppsss!!!! Please try again later";
                    $update = $this->admin->updateUser($user_id, $update);
                    if(true === $update){
                        $response = array(status => STATUS_OK, message => "$user_category has been successfully updated");
                    }
                }
            }

        }
        $this->respond($response);
    }

    private function createNormalUser($payload)
    {
        $response = array(status => STATUS_FAILED_DEPENDENCY, message => "User ID, API Username, Portal Username or Email Address is already in use");
        $user_id = $payload["userId"];
        $portal_username = $payload["portalUsername"];
        $api_username = $payload["apiUsername"];
        $email_address = $payload["emailAddress"];

        $existing_user = $this->admin->getDetailsSpecial(COL_ID . " = '$user_id' or " . COL_PORTAL_USERNAME . " = '$portal_username' or " . COL_API_USERNAME . " = '$api_username' or " .COL_EMAIL_ADDRESS . " = '$email_address'");
        if (!$existing_user) {
            $type = $payload["type"];
            $response[message] = "type can either be Prepaid or Postpaid";
            if (in_array($type, array(USER_TYPE_PREPAID, USER_TYPE_POSTPAID))) {
                $user_creation = $this->admin->createUser(array(COL_CATEGORY => CATEGORY_USER, COL_ID => $user_id, COL_PORTAL_USERNAME => $portal_username, COL_PORTAL_PASSWORD => $payload["portalPassword"],
                    COL_API_PASSWORD => $payload["apiPassword"], COL_API_USERNAME => $payload["apiUsername"], COL_TYPE => $payload["type"],
                    COL_COMPANY_NAME => $payload["companyName"], COL_EMAIL_ADDRESS => $email_address));
                $response[message] = "An error occurred. Please try again later";
                if (true == $user_creation) {
                    $response = array(status => STATUS_OK, message => "User has been created successfully");
                }
            }

        }
        return $response;

    }

    private function createAdmin($payload)
    {
        $response = array(status => STATUS_FAILED_DEPENDENCY, message => "User ID, Portal Username or Email Address is already in use");
        $user_id = $payload["userId"];
        $portal_username = $payload["portalUsername"];
        $email_address = $payload["emailAddress"];
        $existing_user = $this->admin->getDetailsSpecial(COL_ID . " = '$user_id' or " . COL_PORTAL_USERNAME . " = '$portal_username' or " .COL_EMAIL_ADDRESS ." = '$email_address'");
        if (!$existing_user) {
            $user_creation = $this->admin->createUser(array(COL_CATEGORY => CATEGORY_ADMIN, COL_ID => $user_id, COL_PORTAL_USERNAME => $portal_username,
                COL_PORTAL_PASSWORD => $payload["portalPassword"], COL_COMPANY_NAME => "RedTech PLC", COL_EMAIL_ADDRESS => $email_address));
            $response[message] = "An error occurred. Please try again later";
            if (true == $user_creation) {
                $response = array(status => STATUS_OK, message => "Admin has been created successfully");
            }
        }
        return $response;
    }


}
