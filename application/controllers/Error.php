<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Error
 *
 * @author GABRIEL
 */
class Error extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        parent::respond(
                array(
                    status => STATUS_OK, 
                    extra => "You are welcome. Kindly indicate the uri to route your request to"
                )
        );
    }

    function show404() {
        parent::respond(array(status => STATUS_NOT_FOUND));
    }

}
