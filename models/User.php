<?php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $created_at;

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
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->role = $row['role'] ?? 'user';
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->role = $row['role'] ?? 'user';
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (username, email, password, role) 
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        // Password will be hashed, no need to sanitize
        $this->role = htmlspecialchars(strip_tags($this->role ?? 'user'));
        
        // Hash password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Bind parameters
        $stmt->bindParam(1, $this->username);
        $stmt->bindParam(2, $this->email);
        $stmt->bindParam(3, $password_hash);
        $stmt->bindParam(4, $this->role);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function validateLogin() {
        // Save the input password before calling findByEmail
        $input_password = $this->password;
        
        // Check if the email exists and load user data
        if (!$this->findByEmail($this->email)) {
            return false;
        }
        
        // After findByEmail, $this->password now contains the stored hash
        return password_verify($input_password, $this->password);
    }

    public function getFavorites() {
        $query = "SELECT m.* 
                 FROM favorites f 
                 JOIN movies m ON f.movie_id = m.id 
                 WHERE f.user_id = ?";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt;
    }

    public function addFavorite($movieId) {
        // First check if already in favorites
        $checkQuery = "SELECT id FROM favorites WHERE user_id = ? AND movie_id = ? LIMIT 0,1";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(1, $this->id);
        $checkStmt->bindParam(2, $movieId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Already exists
            return true;
        }
        
        $query = "INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $movieId);
        
        return $stmt->execute();
    }

    public function removeFavorite($movieId) {
        $query = "DELETE FROM favorites WHERE user_id = ? AND movie_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $movieId);
        
        return $stmt->execute();
    }

    public function getBookings() {
        $query = "SELECT b.*, s.start_time, s.hall, s.price, m.title as movie_title
                 FROM bookings b
                 JOIN sessions s ON b.session_id = s.id
                 JOIN movies m ON s.movie_id = m.id
                 WHERE b.user_id = ?
                 ORDER BY s.start_time DESC";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt;
    }

    public function getRecommendations() {
        try {
            // Проверяем, есть ли у пользователя бронирования или избранное
            $checkQuery = "SELECT COUNT(*) as count 
                FROM (
                    SELECT 1 FROM bookings WHERE user_id = ?
                    UNION 
                    SELECT 1 FROM favorites WHERE user_id = ?
                ) as user_activity";
            
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(1, $this->id);
            $checkStmt->bindParam(2, $this->id);
            $checkStmt->execute();
            
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $hasActivity = ($row && isset($row['count']) && $row['count'] > 0);
            
            if (!$hasActivity) {
                // Если у пользователя нет активности, возвращаем самые популярные фильмы
                $query = "SELECT m.* 
                        FROM movies m 
                        ORDER BY m.rating DESC, m.year DESC 
                        LIMIT 5";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                
                return $stmt;
            }
            
            // Если у пользователя есть активность, пробуем упрощенные рекомендации по жанрам
            $genreQuery = "SELECT DISTINCT m.genre FROM (
                SELECT m.genre
                FROM bookings b
                JOIN sessions s ON b.session_id = s.id
                JOIN movies m ON s.movie_id = m.id
                WHERE b.user_id = ?
                
                UNION
                
                SELECT m.genre
                FROM favorites f
                JOIN movies m ON f.movie_id = m.id
                WHERE f.user_id = ?
            ) as genres";
            
            $genreStmt = $this->conn->prepare($genreQuery);
            $genreStmt->bindParam(1, $this->id);
            $genreStmt->bindParam(2, $this->id);
            $genreStmt->execute();
            
            $genres = [];
            while ($genreRow = $genreStmt->fetch(PDO::FETCH_ASSOC)) {
                if (isset($genreRow['genre']) && !empty($genreRow['genre'])) {
                    $genres[] = $genreRow['genre'];
                }
            }
            
            if (count($genres) > 0) {
                // Если нашли жанры, ищем фильмы этих жанров
                $placeholders = implode(',', array_fill(0, count($genres), '?'));
                
                $query = "SELECT * FROM movies 
                        WHERE genre IN ($placeholders) 
                        ORDER BY rating DESC, year DESC 
                        LIMIT 5";
                
                $stmt = $this->conn->prepare($query);
                
                // Привязка параметров жанров
                foreach ($genres as $index => $genre) {
                    $stmt->bindValue($index + 1, $genre);
                }
                
                $stmt->execute();
            } else {
                // Запасной вариант - просто самые популярные фильмы
                $query = "SELECT * FROM movies ORDER BY rating DESC, year DESC LIMIT 5";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
            }
            
            return $stmt;
        } catch (Exception $e) {
            // Логируем ошибку
            error_log("Error in getRecommendations: " . $e->getMessage());
            
            // Возвращаем запасной вариант - популярные фильмы
            $fallbackQuery = "SELECT * FROM movies ORDER BY rating DESC LIMIT 3";
            $fallbackStmt = $this->conn->prepare($fallbackQuery);
            $fallbackStmt->execute();
            
            return $fallbackStmt;
        }
    }
} 