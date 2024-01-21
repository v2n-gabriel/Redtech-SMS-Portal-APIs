<?php

class User extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function getAll(){
        $param = $this->getGetParams();
        $user_category = isset($param["userCategory"]) ? $param["userCategory"] : false;
    }

}