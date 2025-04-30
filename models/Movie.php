<?php

class Movie {
    private $conn;
    private $table_name = "movies";

    public $id;
    public $title;
    public $description;
    public $duration;
    public $poster_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
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
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->duration = $row['duration'];
            $this->poster_url = $row['poster_url'];
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