<?php

class Booking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $session_id;
    public $customer_name;
    public $seat_number;
    public $customer_email;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->session_id = $row['session_id'];
            $this->customer_name = $row['customer_name'];
            $this->seat_number = $row['seat_number'];
            $this->customer_email = isset($row['customer_email']) ? $row['customer_email'] : null;
            return true;
        }
        
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (session_id, customer_name, seat_number, customer_email)
                VALUES
                (:session_id, :customer_name, :seat_number, :customer_email)";
        
        $stmt = $this->conn->prepare($query);

        $this->session_id = htmlspecialchars(strip_tags($this->session_id));
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->seat_number = htmlspecialchars(strip_tags($this->seat_number));
        $this->customer_email = isset($this->customer_email) ? htmlspecialchars(strip_tags($this->customer_email)) : null;

        $stmt->bindParam(":session_id", $this->session_id);
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":seat_number", $this->seat_number);
        $stmt->bindParam(":customer_email", $this->customer_email);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function isSeatAvailable($session_id, $seat_number) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                WHERE session_id = :session_id AND seat_number = :seat_number";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->bindParam(":seat_number", $seat_number);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] == 0;
    }
} 