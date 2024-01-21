<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @param null $url
 * @return mixed|string
 */


function triggerGetRequest($url, $header = array()) {
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('log.log', 'w+');
//        log_message("debug", "verbose output ". var_export($verbose, true));
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        $response = curl_exec($ch);
        curl_close($ch);
    } catch (Exception $ex) {
        $response = $ex->getMessage();
    }
    return $response;
}

/**
 * @param null $url
 * @param array $payload
 * @param array $header
 * @return mixed|string
 */
function triggerPostRequest($url = null, $payload = null, $header = array()) {
//    log_message("debug", "calling url => $url\npayload => $payload\nHeader => " .json_encode($header));
//    logString("payload is ".json_encode($payload));
    //$start = microtime(true);
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('log.log', 'w+');
        //log_message("debug", "verbose output ". var_export($verbose));
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        $response = curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {
        $response = $e->getMessage();
    }
//    logString("request took ". (microtime(true) - $start));
//    logString("response is $response");

    return $response;
}
