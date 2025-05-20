<?php

class Database {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private string $port;
    private array $conf;
    public ?PDO $conn;

    public function __construct()
    {
        $this->conf = require_once('db_config.php');
        $this->host = $this->conf['host'];
        $this->db_name = $this->conf['db_name'];
        $this->username = $this->conf['username'];
        $this->password = $this->conf['password'];
        $this->port = $this->conf['port'];
    }

    public function getConnection(): ?PDO
    {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Database Error: " . $exception->getMessage());
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
} 