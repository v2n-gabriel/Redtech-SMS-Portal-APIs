<?php

class PortalSession
{

    const PORTAL_API_SESSION_DURATION = 600;//60mins
    const SESSION_DURATION = 50;//5mins

    private $sessionRepository, $environment;
    var $userId,$portalAPIId;

    function __construct($environment = ENV_DEV)
    {
        $this->environment = $environment;
        $this->sessionRepository = new Table( TABLE_SESSION, false);
    }

    function createSession($user_id, $user_category, $session_id)
    {
//        $reset_sql = "update {table} set " . COL_STATUS . " = '" . STATUS_INACTIVE . "' where " . COL_USER_ID . " = '$user_id' and " . COL_CATEGORY . " = '$user_category' and " . COL_STATUS . " = '" . STATUS_ACTIVE . "'";
//        $this->sessionRepository->executeTableQuery($reset_sql);
        $this->sessionRepository->create(array(COL_CATEGORY => $user_category, COL_USER_ID => $user_id, COL_SESSION_ID => $session_id, COL_ENVIRONMENT => $this->environment));
        return CATEGORY_PORTALAPI == $user_category ? self::PORTAL_API_SESSION_DURATION : self::SESSION_DURATION;
    }

    function checkSession($portal_auth, $user_category = false, $user_auth = false)
    {
        //validate portal auth
        $sql = "select ".COL_SESSION_ID.",". COL_USER_ID." from {table} where ". COL_ENVIRONMENT . " = '$this->environment' and " . COL_CATEGORY . " = '" . CATEGORY_PORTALAPI . "' and " . COL_STATUS . " = '" . STATUS_ACTIVE . "' and session_id = '$portal_auth' and date_modified + interval '" . self::PORTAL_API_SESSION_DURATION . "' minute >= current_timestamp";
        $portal_session = $this->sessionRepository->executeTableQuery($sql, true);
        if ($portal_session) {
            $this->portalAPIId = $portal_session[0][COL_USER_ID];
            $this->sessionRepository->executeTableQuery("update {table} set date_modified = current_timestamp where session_id = '$portal_auth'");
        } else {
            respond(array(status => 900, message => "Portal auth has expired"));
        }

        if (false != $user_category) {
            $category_clause = is_array($user_category) ? COL_CATEGORY . " in ('".implode("','", $user_category)."')" : COL_CATEGORY . " = '$user_category'";
            //validate user auth
            $sql = "select * from {table} where ". COL_ENVIRONMENT . " = '$this->environment' and  $category_clause and " . COL_STATUS . " = '" . STATUS_ACTIVE . "' and ".COL_SESSION_ID." = '$user_auth' and ".COL_DATE_MODIFIED." + interval '" . self::SESSION_DURATION . "' minute >= current_timestamp";
            $session_details = $this->sessionRepository->executeTableQuery($sql, true);
            if ($session_details) {
                $this->userId = $session_details[0][COL_USER_ID];
                $this->sessionRepository->executeTableQuery("update {table} set date_modified = current_timestamp where session_id = '$user_auth'");
            } else {
                respond(array(status => 901, message => "Session has expired"));
            }

        }
    }

    function switchUserSessionAcrossEnvironment($session_id,  $from, $to){
        $this->sessionRepository->update(array(COL_SESSION_ID => $session_id, COL_ENVIRONMENT => $from), array(COL_ENVIRONMENT => $to));
    }

    function getUserActiveSession($id){
        $session = $this->sessionRepository->executeTableQuery("select ". COL_SESSION_ID." from {table} where ".COL_USER_ID." = '{$id}'  and ". COL_ENVIRONMENT . " = '$this->environment' and " . COL_STATUS . " = '" . STATUS_ACTIVE."' and ". COL_DATE_MODIFIED." + interval '" . self::PORTAL_API_SESSION_DURATION . "' minute >= current_timestamp", true);
        return $session ? $session[0][COL_SESSION_ID] : false;
    }


    function endSession($user_auth)
    {
        $this->sessionRepository->update(array(COL_SESSION_ID => $user_auth, COL_ENVIRONMENT => $this->environment), array(COL_STATUS => STATUS_INACTIVE));
    }

}