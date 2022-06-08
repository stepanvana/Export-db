<?php

//
header('Access-Control-Allow-Origin: *');
header("Content-Type: text/html;charset=UTF-8");

//Require classes
include_once 'config/Database.php';
include_once 'models/API.php';

//Connect to database
$database = new Database();
$db = $database->connect();

$api = new API($db);