<?php

class Booking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $session_id;
    public $customer_name;
    public $seat_number;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (session_id, customer_name, seat_number)
                VALUES
                (:session_id, :customer_name, :seat_number)";
        
        $stmt = $this->conn->prepare($query);

        $this->session_id = htmlspecialchars(strip_tags($this->session_id));
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->seat_number = htmlspecialchars(strip_tags($this->seat_number));

        $stmt->bindParam(":session_id", $this->session_id);
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":seat_number", $this->seat_number);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
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