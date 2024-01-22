<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route["default_controller"] = "error";
$route["404_override"] = "error/show404";
$route["translate_uri_dashes"] = FALSE;

//service APIs
$route["(:any)/meta/(:any)"]["get"] = "$1/meta/$2";



$route["dev/(:any)/terminate"]["post"] = "dev/$1";
$route["live/(:any)/terminate"]["post"] = "v1/$1";

$route["dev"]["get"] = $route["dev"]["post"] = $route["live"]["get"] = $route["live"]["post"] = "error";



//portal APIs
$route["portal/(:any)/authenticate"]["get"] = $route["portal/(:any)/authenticate"]["post"] = "portal/authentication/login/$1";
$route["portal/(:any)/getPortalActiveSession"]["get"] = "portal/authentication/getPortalActiveSession/$1";
$route["portal/(:any)/logout"]["get"] = "portal/authentication/logout/$1";
$route["portal/(:any)/switchSession"]["get"] = "portal/SwitchSession/switchIt/$1";
//superAdmin
$route["portal/(:any)/superAdmin/user/getCategory"]["get"] = "portal/$1/superAdmin/user/getAllUserCategory";
$route["portal/(:any)/superAdmin/user"]["get"] = "portal/$1/superAdmin/user/index";
$route["portal/(:any)/superAdmin/user/create"]["post"] = "portal/$1/superAdmin/user/create";
$route["portal/(:any)/superAdmin/user/update"]["post"] = "portal/$1/superAdmin/user/update";
$route["portal/(:any)/(:any)/biller/category"]["get"] = "portal/$1/$2/biller/category";
$route["portal/(:any)/(:any)/biller"]["get"] = "portal/$1/$2/biller/getAll";
$route["portal/(:any)/(:any)/userBiller"]["get"] = "portal/$1/$2/biller/getUserBiller";
$route["portal/(:any)/(:any)/userBiller/configure"]["post"] = "portal/$1/$2/biller/configure";


$route["portal/(:any)/(:any)/log/(:any)"]["get"] = "portal/$1/$2/log/$3";
$route["portal/(:any)/(:any)/logCount"]["get"] = "portal/$1/$2/logCount/index";
$route["portal/(:any)/(:any)/profile/getDetails"]["get"] = "portal/$1/$2/profile/getDetails";


//batch sms pushing from the portal
$route["portal/(:any)/(:any)/sms/(:any)"]["post"] = "portal/$1/$2/sms/$3";

