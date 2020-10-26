<?php

//
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

//Require classes
include_once 'config/Database.php';
include_once 'models/Api_key.php';
include_once 'models/API.php';

//Check for invalid api keys and fields
$get_api_key = $_GET['api_key'];
$name = $_GET['name'];

if(!isset($get_api_key)) {
    echo json_encode(
        array(
            'code'    => 1,
            'message' => 'API key is required.'
        )
    );

    die();
}

if(strlen($get_api_key) != 100) {
    echo json_encode(
        array(
            'code'    => 2,
            'message' => strlen($get_api_key) . 'API key is invalid.'
        )
    );

    die();
}

if(!isset($name)) {
    echo json_encode(
        array(
            'code'    => 3,
            'message' => 'Name is required.'
        )
    );

    die();
}

//Connect to database
$database = new Database();
$db = $database->connect();

//Validate api key
$api_key = new Api_key($db);
$api_key_result = $api_key->read($get_api_key);
$api_key_num = $api_key_result->rowCount();

if($api_key_num == 0) {
    echo json_encode(
        array(
            'code'    => 4,
            'message' => 'API key is invalid.'
        )
    );

    die();
}

$api = new API($db);
$api->my_vocative($name);