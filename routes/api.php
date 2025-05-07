<?php

// Debug information
error_log("Starting API routing...");
error_log("Current directory: " . __DIR__);

// Load required files
$files = [
    __DIR__ . '/../controllers/BaseController.php',
    __DIR__ . '/../config/database.php',
    __DIR__ . '/../models/Movie.php',
    __DIR__ . '/../models/Session.php',
    __DIR__ . '/../models/Booking.php',
    __DIR__ . '/../models/User.php',
    __DIR__ . '/../controllers/MovieController.php',
    __DIR__ . '/../controllers/SessionController.php',
    __DIR__ . '/../controllers/BookingController.php',
    __DIR__ . '/../controllers/UserController.php',
    __DIR__ . '/../controllers/AdminController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        error_log("Loading file: " . $file);
        require_once $file;
    } else {
        error_log("File not found: " . $file);
    }
}

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug information
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("PHP_SELF: " . $_SERVER['PHP_SELF']);
error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);

// Parse request path
$basePath = '/api';
$requestUri = $_SERVER['REQUEST_URI'];
$path = str_replace($basePath, '', $requestUri);
$path = trim($path, '/');
$segments = $path ? explode('/', $path) : [];

error_log("Processed path: " . $path);
error_log("Segments: " . print_r($segments, true));

// Get request method and query parameters
$requestMethod = $_SERVER["REQUEST_METHOD"];
$queryParams = $_GET;

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Route handling
try {
    // Check if path is empty
    if (empty($segments)) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Resource not found'
        ]);
        exit();
    }

    // Route to appropriate controller
    switch ($segments[0]) {
        case 'movies':
            $controller = new MovieController($db, $requestMethod);
            if (isset($segments[1]) && is_numeric($segments[1])) {
                $controller->setMovieId($segments[1]);
            }
            $controller->processRequest();
            break;
            
        case 'sessions':
            $controller = new SessionController($db, $requestMethod);
            if (isset($segments[1]) && is_numeric($segments[1])) {
                $controller->setSessionId($segments[1]);
            } elseif (isset($queryParams['movie_id'])) {
                $controller->setMovieId($queryParams['movie_id']);
            }
            $controller->processRequest();
            break;
            
        case 'bookings':
            $controller = new BookingController($db, $requestMethod);
            if (isset($segments[1]) && is_numeric($segments[1])) {
                $controller->setBookingId($segments[1]);
            }
            $controller->processRequest();
            break;
            
        case 'users':
            $controller = new UserController($db, $requestMethod);
            
            if (isset($segments[1]) && is_numeric($segments[1])) {
                $userId = $segments[1];
                $controller->setUserId($userId);
                
                // Handle sub-resources for users
                if (isset($segments[2])) {
                    switch ($segments[2]) {
                        case 'favorites':
                            if ($requestMethod === 'GET') {
                                $controller->getUserFavorites($userId);
                            } elseif ($requestMethod === 'POST') {
                                // Add to favorites
                                $data = json_decode(file_get_contents("php://input"));
                                if (isset($data->movieId)) {
                                    $controller->addToFavorites($userId, $data->movieId);
                                } else {
                                    http_response_code(400);
                                    echo json_encode(['status' => 'error', 'message' => 'Movie ID required']);
                                }
                            } elseif ($requestMethod === 'DELETE' && isset($segments[3]) && is_numeric($segments[3])) {
                                // Remove from favorites
                                $controller->removeFromFavorites($userId, $segments[3]);
                            } else {
                                http_response_code(405);
                                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                            }
                            exit();
                            
                        case 'bookings':
                            if ($requestMethod === 'GET') {
                                $controller->getUserBookings($userId);
                            } else {
                                http_response_code(405);
                                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                            }
                            exit();
                            
                        case 'recommendations':
                            if ($requestMethod === 'GET') {
                                $controller->getUserRecommendations($userId);
                            } else {
                                http_response_code(405);
                                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                            }
                            exit();
                            
                        default:
                            http_response_code(404);
                            echo json_encode(['status' => 'error', 'message' => 'Resource not found']);
                            exit();
                    }
                }
            }
            
            $controller->processRequest();
            break;
            
        case 'register':
            if ($requestMethod !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                exit();
            }
            
            $controller = new UserController($db, $requestMethod);
            $controller->processRequest();
            break;
            
        case 'login':
            if ($requestMethod !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                exit();
            }
            
            $controller = new UserController($db, $requestMethod);
            $controller->login();
            exit();
            
        case 'admin':
            // Implement authorization check for admin
            // This is a placeholder - implement proper authorization
            
            $controller = new AdminController($db, $requestMethod);
            $controller->processRequest();
            break;
            
        case 'pricing':
            if ($requestMethod !== 'PUT') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                exit();
            }
            
            // Implement authorization check for admin
            // This is a placeholder - implement proper authorization
            
            $controller = new AdminController($db, $requestMethod);
            $_GET['action'] = 'pricing';
            $controller->processRequest();
            break;
            
        case 'statistics':
            if ($requestMethod !== 'GET') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                exit();
            }
            
            // Implement authorization check for admin
            // This is a placeholder - implement proper authorization
            
            $controller = new AdminController($db, $requestMethod);
            $_GET['action'] = 'statistics';
            $controller->processRequest();
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Resource not found'
            ]);
            exit();
    }
} catch (Exception $e) {
    error_log('Routing Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
    exit();
} 