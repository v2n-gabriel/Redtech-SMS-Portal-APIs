<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = "mysql_redtech.174";
$query_builder = TRUE;

$db["mysql_redtech.174"] = array(
    'dsn' => 'mysql:host=192.164.177.174;port=3306;dbname=redtech',
    'hostname' => '',
    'username' => 'redtech',
    'password' => 'RED4gHNtv2T8n',
    'database' => 'redtech',
    'dbdriver' => 'pdo',
    'dbprefix' => '',
    'pconnect' => TRUE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE,
    'autoinit' => TRUE,
);
