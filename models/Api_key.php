<?php

class Api_key {    
    private $conn;
    private $table = 'api_keys';

    public $id;
    public $api_key;
    public $is_valid;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($api_key) {
        $query = "SELECT created_at FROM $this->table WHERE api_key = '$api_key' AND is_valid = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}