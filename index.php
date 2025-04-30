<?php

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default headers
header('Content-Type: application/json');

// Get request information
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle root path
if ($requestUri === '/') {
    echo json_encode([
        'status' => 'success',
        'message' => 'Welcome to Cinema API',
        'endpoints' => [
            'GET /api/movies' => 'Get all movies',
            'GET /api/movies/{id}' => 'Get movie by ID',
            'GET /api/sessions' => 'Get all sessions',
            'GET /api/sessions/{id}' => 'Get session by ID',
            'POST /api/bookings' => 'Create a new booking',
            'GET /api/bookings/{id}' => 'Get booking by ID'
        ]
    ]);
    exit();
}

// Handle API routes
try {
    require_once __DIR__ . '/routes/api.php';
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
    exit();
}

// If we reach here, no route was matched
http_response_code(200);
echo json_encode(['error' => 'Not found']); 