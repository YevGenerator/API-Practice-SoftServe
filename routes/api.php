<?php

// Load required files
require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Session.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../controllers/MovieController.php';
require_once __DIR__ . '/../controllers/SessionController.php';
require_once __DIR__ . '/../controllers/BookingController.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

// Get request method
$requestMethod = $_SERVER["REQUEST_METHOD"];

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
            $controller->processRequest();
            break;
            
        case 'sessions':
            $controller = new SessionController($db, $requestMethod);
            $controller->processRequest();
            break;
            
        case 'bookings':
            $controller = new BookingController($db, $requestMethod);
            $controller->processRequest();
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Resource not found'
            ]);
            break;
    }
} catch (Exception $e) {
    error_log('Routing Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
} 