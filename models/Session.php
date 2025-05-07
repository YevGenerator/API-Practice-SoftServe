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

    public function read() {
        $query = "SELECT s.*, m.title as movie_title, m.genre 
                 FROM " . $this->table_name . " s
                 JOIN movies m ON s.movie_id = m.id";
        
        // Add filters if provided
        $params = [];
        $conditions = [];
        
        if (!empty($_GET['date'])) {
            $date = $_GET['date'];
            $conditions[] = "DATE(s.start_time) = ?";
            $params[] = $date;
        }
        
        if (!empty($_GET['time'])) {
            $time = $_GET['time'];
            $conditions[] = "TIME(s.start_time) >= ?";
            $params[] = $time;
        }
        
        if (!empty($_GET['genre'])) {
            $genre = $_GET['genre'];
            $conditions[] = "m.genre = ?";
            $params[] = $genre;
        }
        
        if (!empty($_GET['movieId'])) {
            $movieId = $_GET['movieId'];
            $conditions[] = "s.movie_id = ?";
            $params[] = $movieId;
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY s.start_time ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        return $stmt;
    }

    public function readByMovie($movie_id) {
        error_log("Executing query for movie_id: " . $movie_id);
        $query = "SELECT * FROM " . $this->table_name . " WHERE movie_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $movie_id);
        $stmt->execute();
        error_log("Query executed, row count: " . $stmt->rowCount());
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

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (movie_id, start_time, hall, price) 
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->movie_id = (int)$this->movie_id;
        $this->hall = htmlspecialchars(strip_tags($this->hall));
        $this->price = (float)$this->price;
        
        // Bind parameters
        $stmt->bindParam(1, $this->movie_id);
        $stmt->bindParam(2, $this->start_time);
        $stmt->bindParam(3, $this->hall);
        $stmt->bindParam(4, $this->price);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET movie_id = ?, start_time = ?, hall = ?, price = ? 
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->movie_id = (int)$this->movie_id;
        $this->hall = htmlspecialchars(strip_tags($this->hall));
        $this->price = (float)$this->price;
        $this->id = (int)$this->id;
        
        // Bind parameters
        $stmt->bindParam(1, $this->movie_id);
        $stmt->bindParam(2, $this->start_time);
        $stmt->bindParam(3, $this->hall);
        $stmt->bindParam(4, $this->price);
        $stmt->bindParam(5, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->id = (int)$this->id;
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
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