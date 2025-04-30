<?php

class Session {
    private $conn;
    private $table_name = "sessions";

    public $id;
    public $movie_id;
    public $start_time;
    public $hall;
    public $price;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readByMovie($movie_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE movie_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $movie_id);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->movie_id = $row['movie_id'];
            $this->start_time = $row['start_time'];
            $this->hall = $row['hall'];
            $this->price = $row['price'];
            return true;
        }
        return false;
    }

    public function getBookedSeats($sessionId) {
        $stmt = $this->conn->prepare("
            SELECT seat_number 
            FROM bookings 
            WHERE session_id = ?
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function isSeatAvailable($sessionId, $seatNumber) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM bookings 
            WHERE session_id = ? AND seat_number = ?
        ");
        $stmt->execute([$sessionId, $seatNumber]);
        return $stmt->fetchColumn() == 0;
    }
} 