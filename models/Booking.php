<?php

class Booking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $session_id;
    public $user_id;
    public $seat_number;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByUser($user_id) {
        $query = "SELECT b.*, s.start_time, s.hall, s.price, m.title as movie_title 
                 FROM " . $this->table_name . " b
                 JOIN sessions s ON b.session_id = s.id
                 JOIN movies m ON s.movie_id = m.id
                 WHERE b.user_id = ?
                 ORDER BY s.start_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->session_id = $row['session_id'];
            $this->user_id = $row['user_id'];
            $this->seat_number = $row['seat_number'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (session_id, user_id, seat_number)
                VALUES
                (?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->session_id = (int)$this->session_id;
        $this->user_id = (int)$this->user_id;
        $this->seat_number = htmlspecialchars(strip_tags($this->seat_number));

        // Bind parameters
        $stmt->bindParam(1, $this->session_id);
        $stmt->bindParam(2, $this->user_id);
        $stmt->bindParam(3, $this->seat_number);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $this->id = (int)$this->id;
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function isSeatAvailable($session_id, $seat_number) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                WHERE session_id = ? AND seat_number = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $session_id);
        $stmt->bindParam(2, $seat_number);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] == 0;
    }
    
    public function getBookedSeats($session_id) {
        $query = "SELECT seat_number FROM " . $this->table_name . "
                WHERE session_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $session_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} 