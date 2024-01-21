<?php

/**
 * Created by PhpStorm.
 * User: GABRIEL
 * Date: 23/05/2018
 * Time: 11:55
 */


//default response indexes
const status = "status", message = "message", description = "description", extra = "extra", data = "data",
referenceId = "referenceId";
//status codes
const STATUS_OK = 200, STATUS_UNAUTHORIZED = 401, STATUS_FORBIDDEN = 403, STATUS_NOT_FOUND = 404,
        STATUS_METHOD_NOT_ALLOWED = 405, STATUS_INTERNAL_SERVER_ERROR = 500, STATUS_BAD_GATEWAY = 502,
        STATUS_HTTP_NOT_SUPPORTED = 505, STATUS_LOGIN_TIME_OUT = 440, STATUS_FAILED_DEPENDENCY = 412;


function generateUniqueID($length = 18) {
    $time = str_replace('.', '', (string)microtime(true));
    if (!is_numeric($length)) {
        $length = 18;
    }
    $characters = '0123456789';
    while (true) {
        $time .= $characters[mt_rand(0, strlen($characters) - 1)];
        if (strlen($time) >= $length) {
            break;
        }
    }
    return $time;
}

function logString($string, $directory = null) {
    if ($directory) {
        $today_date = date("Y-m-d");
        $timestamp = date("Y-m-d H:i:s");
        $log_file = "./application/logs/$directory/log-$today_date.log";
        file_put_contents($log_file, "$timestamp ==> $string\n\n", FILE_APPEND);
    } else {
        log_message("debug", $string . "\n");
    }
}

function xml_to_array($sdp_response = "") {
    //log_message("debug", "sdp response <<>> $sdp_response \n");
    $xml = str_ireplace(array('soapenv:', 'env:', 'soap:', 'ns1:', 'ns2:'), '', $sdp_response);
    //log_message("debug", "simplexml_load-string gave <<>> ". var_export(simplexml_load_string($xml),true));
    $response = !$xml ? "" : (array) json_decode(json_encode((array) simplexml_load_string($xml), true));
    //log_message("debug", "xml <<>> $xml \n");
    //$response = ;
    //log_message("debug", "array response <<>> ".var_export($response,true));
    return $response;
}

function camelCaseToSnakeCase($string){
    return preg_replace("/(?<!^)[A-Z]/", "_$0", $string);
}

function snakeCaseToCamelCase($input, $separator = "_")
{
    return lcfirst(str_replace(" ", "", ucwords(str_replace($separator, " ",strtolower($input)))));
}


function respond($response_message = array()) {
    $response_description = null;
    $status_code = $response_message[status];
    switch ($status_code) {
        case STATUS_OK:
            $response_description = "request has been processed";
            break;
        case STATUS_INTERNAL_SERVER_ERROR :
            $response_description = "error encountered";
            break;
        case STATUS_UNAUTHORIZED :
            $response_description = "access denied";
            break;
        case STATUS_NOT_FOUND :
            $response_description = "wrong request method or wrong http address";
            break;
        case STATUS_FAILED_DEPENDENCY :
            $response_description = "precondition failed";
            break;
    }

    $response_message[description] = $response_description;
    $response_message = json_encode($response_message);

//        switch (self::$contentType) {
//            case "application/json" :
//                $response_message = json_encode($response_message);
//                break;
//        }

    logString("response to client is $response_message \n\n");
    //$this->output->set_content_type(self::$contentType)
    //        ->set_status_header($status_code)
    //        ->set_output($response_message);
    header("HTTP/1.1 $status_code");
    header("Content-type:application/json");
    echo $response_message;
    exit();
}

function getMobileNetworkOperator($phone_number) {
    if(("+234" === substr($phone_number, 0, 4) && 14 == strlen($phone_number)) ||
        ("234" === substr($phone_number, 0, 3) && 13 == strlen($phone_number)) ||
        ("0" === substr($phone_number, 0, 1) && 11 == strlen($phone_number)) ||
        (in_array(substr($phone_number, 0, 1), array(7,8,9)) && 10 == strlen($phone_number)) ){

        $msisdn = substr($phone_number, -10);
        $number_series = array(
            "mtn" => array('906', '903', '816', '814', '813', '810', '806', '803', '706', '703', '7025', '7026', '704', '913', '916'),
            "airtel" => array('907', '902', "901", '812', '808', '802', '708', '701', '904', '912', '911'),
            "glo" => array('905', '815', '811', '807', '805', '705', '915'),
            "9mobile" => array('908', '909', '818', '817', '809'),
        );

        foreach ($number_series as $network => $series){
            if(in_array(substr($msisdn,0, 3), $series) || in_array(substr($msisdn,0, 4), $series)){
                return $network;
            }
        }
    }
    return false;
}
