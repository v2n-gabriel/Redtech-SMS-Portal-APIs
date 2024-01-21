<?php

class Admin extends UserLib
{
    private $userTypes;
    private $users;
    function __construct($environment = ENV_DEV)
    {
        parent::__construct($environment);
        $this->userTypes = new Table(TABLE_USER_CATEGORY,$environment);
        $this->users = new Table(TABLE_USER, $environment);
    }

    function getAllUserCategory(){
        $types_to_exclude = array(USER_CATEGORY_SUPERADMIN, USER_CATEGORY_PORTALAPI);
        return array_column($this->userTypes->read(array("where" => COL_ID ." not in ('". implode("','",$types_to_exclude)."')")), "id");
    }

    function createUser($user){
        return $this->users->create($user);
    }

    function getDetailsSpecial($where){
        return $this->users->read(array("where" => $where));
    }

    function updateUser($user_id, $update){
        return $this->users->update(array(COL_ID => $user_id), $update);
    }

    function getAllUsers($user_id = false){
        $types_to_exclude = array(USER_CATEGORY_SUPERADMIN, USER_CATEGORY_PORTALAPI);

        $users = array();
        $condition = COL_CATEGORY ." not in ('". implode("','",$types_to_exclude)."')";
        if(false !== $user_id){
            $condition .= " and ".COL_ID . " = '$user_id'";
        }
        $detailss = $this->users->read(array("where" => $condition));
        foreach ($detailss as $details){
            $user = array("userId" => $details[COL_ID], "name" => $details[COL_COMPANY_NAME], "category" => $details[COL_CATEGORY], "apiUsername" => $details[COL_API_USERNAME], "portalUsername" => $details[COL_PORTAL_USERNAME], status => $details[COL_STATUS], "type" => $details[COL_TYPE], "emailAddress" => $details[COL_EMAIL_ADDRESS]);

            if(false !== $user_id){
                $user = array_merge($user, array("apiPassword" => $details[COL_API_PASSWORD], "portalPassword" => $details[COL_PORTAL_PASSWORD]));
                if(USER_CATEGORY_ADMIN == $user["category"]){
                    unset($user["apiUsername"]);
                    unset($user["apiPassword"]);
                }
            }
            $users[] = $user;
        }
        return $users;

    }

}