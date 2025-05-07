<?php

class Movie {
    private $conn;
    private $table_name = "movies";

    public $id;
    public $title;
    public $description;
    public $duration;
    public $poster_url;
    public $genre;
    public $year;
    public $rating;
    public $actors;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        
        // Add filters if provided
        $params = [];
        $conditions = [];
        
        if (!empty($_GET['genre'])) {
            $conditions[] = "genre = ?";
            $params[] = $_GET['genre'];
        }
        
        if (!empty($_GET['year'])) {
            $conditions[] = "year = ?";
            $params[] = $_GET['year'];
        }
        
        if (!empty($_GET['rating'])) {
            $conditions[] = "rating >= ?";
            $params[] = $_GET['rating'];
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY year DESC, rating DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->duration = $row['duration'];
            $this->poster_url = $row['poster_url'];
            $this->genre = $row['genre'] ?? '';
            $this->year = $row['year'] ?? null;
            $this->rating = $row['rating'] ?? null;
            $this->actors = $row['actors'] ?? '';
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (title, description, duration, poster_url, genre, year, rating, actors) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->duration = (int)$this->duration;
        $this->poster_url = htmlspecialchars(strip_tags($this->poster_url));
        $this->genre = htmlspecialchars(strip_tags($this->genre));
        $this->year = (int)$this->year;
        $this->rating = (float)$this->rating;
        $this->actors = htmlspecialchars(strip_tags($this->actors));
        
        // Bind parameters
        $stmt->bindParam(1, $this->title);
        $stmt->bindParam(2, $this->description);
        $stmt->bindParam(3, $this->duration);
        $stmt->bindParam(4, $this->poster_url);
        $stmt->bindParam(5, $this->genre);
        $stmt->bindParam(6, $this->year);
        $stmt->bindParam(7, $this->rating);
        $stmt->bindParam(8, $this->actors);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET title = ?, description = ?, duration = ?, poster_url = ?, 
                 genre = ?, year = ?, rating = ?, actors = ? 
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->duration = (int)$this->duration;
        $this->poster_url = htmlspecialchars(strip_tags($this->poster_url));
        $this->genre = htmlspecialchars(strip_tags($this->genre));
        $this->year = (int)$this->year;
        $this->rating = (float)$this->rating;
        $this->actors = htmlspecialchars(strip_tags($this->actors));
        $this->id = (int)$this->id;
        
        // Bind parameters
        $stmt->bindParam(1, $this->title);
        $stmt->bindParam(2, $this->description);
        $stmt->bindParam(3, $this->duration);
        $stmt->bindParam(4, $this->poster_url);
        $stmt->bindParam(5, $this->genre);
        $stmt->bindParam(6, $this->year);
        $stmt->bindParam(7, $this->rating);
        $stmt->bindParam(8, $this->actors);
        $stmt->bindParam(9, $this->id);
        
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

    public function getSessions($movieId) {
        $stmt = $this->conn->prepare("
            SELECT s.* 
            FROM sessions s 
            WHERE s.movie_id = ? 
            ORDER BY s.start_time ASC
        ");
        $stmt->execute([$movieId]);
        return $stmt->fetchAll();
    }
} 