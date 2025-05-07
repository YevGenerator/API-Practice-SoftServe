<?php

class UserController extends BaseController {
    private $user;
    private $userId;

    public function __construct($db, $requestMethod) {
        parent::__construct($db, $requestMethod);
        $this->user = new User($db);
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function processRequest() {
        try {
            switch ($this->requestMethod) {
                case 'GET':
                    if (isset($this->userId)) {
                        $this->getUser($this->userId);
                    } else {
                        $this->setResponse(400, null, 'User ID is required');
                    }
                    break;
                case 'POST':
                    $this->registerUser();
                    break;
                default:
                    $this->setResponse(405, null, 'Method not allowed');
                    break;
            }
        } catch (Exception $e) {
            error_log('UserController Error: ' . $e->getMessage());
            $this->setResponse(500, null, 'Internal server error');
        }
        
        $this->sendResponse();
        exit();
    }

    private function getUser($id) {
        if (!is_numeric($id)) {
            $this->setResponse(400, null, 'Invalid user ID');
            return;
        }

        $this->user->id = (int)$id;
        
        if (!$this->user->readOne()) {
            $this->setResponse(404, null, 'User not found');
            return;
        }

        $userData = [
            'id' => $this->user->id,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'created_at' => $this->user->created_at
        ];

        $this->setResponse(200, $userData);
    }
    
    private function registerUser() {
        // Get request body
        $data = json_decode(file_get_contents("php://input"));
        
        if (
            empty($data->username) || 
            empty($data->email) || 
            empty($data->password)
        ) {
            $this->setResponse(400, null, 'Incomplete data. Username, email, and password are required.');
            return;
        }
        
        // Set user properties
        $this->user->username = $data->username;
        $this->user->email = $data->email;
        $this->user->password = $data->password;
        $this->user->role = $data->role ?? 'user';
        
        // Check if email already exists
        if ($this->user->emailExists()) {
            $this->setResponse(400, null, 'Email already exists');
            return;
        }
        
        if ($this->user->create()) {
            $this->setResponse(201, [
                'id' => $this->user->id,
                'message' => 'User registered successfully'
            ]);
        } else {
            $this->setResponse(500, null, 'Failed to register user');
        }
    }
    
    public function login() {
        // Get request body
        $data = json_decode(file_get_contents("php://input"));
        
        if (
            empty($data->email) || 
            empty($data->password)
        ) {
            $this->setResponse(400, null, 'Email and password are required');
            $this->sendResponse();
            return;
        }
        
        // Set user properties
        $this->user->email = $data->email;
        $this->user->password = $data->password;
        
        // Validate login
        if ($this->user->validateLogin()) {
            $this->setResponse(200, [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'role' => $this->user->role,
                'token' => $this->generateJWT()
            ]);
        } else {
            $this->setResponse(401, null, 'Invalid credentials');
        }
        
        // Send response
        $this->sendResponse();
    }
    
    private function generateJWT() {
        // In a real implementation, you would generate a proper JWT token
        // For simplicity, we're just returning a base64 encoded string
        $payload = [
            'id' => $this->user->id,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'exp' => time() + 3600 // 1 hour expiration
        ];
        
        return base64_encode(json_encode($payload));
    }
    
    public function getUserFavorites($userId) {
        if (!is_numeric($userId)) {
            $this->setResponse(400, null, 'Invalid user ID');
            $this->sendResponse();
            return;
        }
        
        try {
            $this->user->id = (int)$userId;
            
            $result = $this->user->getFavorites();
            $favorites = [];
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $favorites[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'duration' => (int)$row['duration'],
                    'posterUrl' => $row['poster_url'],
                    'genre' => $row['genre'] ?? '',
                    'year' => (int)($row['year'] ?? 0),
                    'rating' => (float)($row['rating'] ?? 0)
                ];
            }
            
            // Унифицированный формат ответа
            $this->setResponse(200, $favorites);
            $this->sendResponse();
        } catch (Exception $e) {
            error_log("Error in getUserFavorites: " . $e->getMessage());
            $this->setResponse(500, null, 'Internal server error');
            $this->sendResponse();
        }
    }
    
    public function addToFavorites($userId, $movieId) {
        if (!is_numeric($userId) || !is_numeric($movieId)) {
            $this->setResponse(400, null, 'Invalid user ID or movie ID');
            return;
        }
        
        $this->user->id = (int)$userId;
        
        // Check if user exists
        if (!$this->user->readOne()) {
            $this->setResponse(404, null, 'User not found');
            return;
        }
        
        if ($this->user->addFavorite((int)$movieId)) {
            $this->setResponse(200, ['message' => 'Movie added to favorites']);
        } else {
            $this->setResponse(500, null, 'Failed to add movie to favorites');
        }
    }
    
    public function removeFromFavorites($userId, $movieId) {
        if (!is_numeric($userId) || !is_numeric($movieId)) {
            $this->setResponse(400, null, 'Invalid user ID or movie ID');
            return;
        }
        
        $this->user->id = (int)$userId;
        
        if ($this->user->removeFavorite((int)$movieId)) {
            $this->setResponse(200, ['message' => 'Movie removed from favorites']);
        } else {
            $this->setResponse(500, null, 'Failed to remove movie from favorites');
        }
    }
    
    public function getUserBookings($userId) {
        if (!is_numeric($userId)) {
            $this->setResponse(400, null, 'Invalid user ID');
            $this->sendResponse();
            return;
        }
        
        try {
            $this->user->id = (int)$userId;
            
            $result = $this->user->getBookings();
            $bookings = [];
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $bookings[] = [
                    'id' => (int)$row['id'],
                    'sessionId' => (int)$row['session_id'],
                    'userId' => (int)$row['user_id'],
                    'seatNumber' => $row['seat_number'],
                    'bookingDate' => $row['created_at'],
                    'movieTitle' => $row['movie_title'],
                    'startTime' => $row['start_time'],
                    'hall' => $row['hall'],
                    'price' => (float)$row['price']
                ];
            }
            
            // Унифицированный формат ответа
            $this->setResponse(200, $bookings);
            $this->sendResponse();
        } catch (Exception $e) {
            error_log("Error in getUserBookings: " . $e->getMessage());
            $this->setResponse(500, null, 'Internal server error');
            $this->sendResponse();
        }
    }
    
    public function getUserRecommendations($userId) {
        if (!is_numeric($userId)) {
            $this->setResponse(400, null, 'Invalid user ID');
            $this->sendResponse();
            return;
        }
        
        try {
            $this->user->id = (int)$userId;
            
            $result = $this->user->getRecommendations();
            $recommendations = [];
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $recommendations[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'duration' => (int)$row['duration'],
                    'posterUrl' => $row['poster_url'] ?? null,
                    'genre' => $row['genre'] ?? '',
                    'year' => (int)($row['year'] ?? 0),
                    'rating' => (float)($row['rating'] ?? 0)
                ];
            }
            
            // Унифицированный формат ответа
            $this->setResponse(200, $recommendations);
            $this->sendResponse();
        } catch (Exception $e) {
            error_log("Error in getUserRecommendations: " . $e->getMessage());
            $this->setResponse(500, null, 'Internal server error');
            $this->sendResponse();
        }
    }
} 