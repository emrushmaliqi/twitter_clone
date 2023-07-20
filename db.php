<?php

class Database {
    private $server = 'localhost';
    private $dbname = 'twitter_clone';
    private $user = 'root';
    private $password = '';

    public function connect() {
        try {
            $conn = new PDO("mysql:host=$this->server;dbname=$this->dbname", $this->user, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (Exception $e) {
            echo "Database Error: ". $e->getMessage();
        }
    }
}
