<?php

namespace App\Database;

class DatabaseConfig {
    public $host;
    public $username;
    public $password;
    public $name;
    public $port;
    
    function __construct($host, $username, $password, $name, $port) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
        $this->port = $port;
    }
}
