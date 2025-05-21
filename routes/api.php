<?php
require_once("HttpRequestMethod.php");
require_once("ApiRoutes.php");
require_once("ErrorMessage.php");
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
// Load required files
$files = [
    __DIR__ . '/../controllers/BaseController.php',
    __DIR__ . '/../config/database.php',
    __DIR__ . '/../jwt_processing/JwtService.php',
    __DIR__ . '/../jwt_processing/AuthUser.php',
    __DIR__ . '/../models/Movie.php',
    __DIR__ . '/../models/Session.php',
    __DIR__ . '/../models/User.php',
    __DIR__ . '/../models/Genres.php',
    __DIR__ . '/../models/Halls.php',
    __DIR__ . '/../models/Ticket.php',
    __DIR__ . '/../models/Actor.php',
    __DIR__ . '/../controllers/MovieController.php',
    __DIR__ . '/../controllers/SessionController.php',
    __DIR__ . '/../controllers/BookingController.php',
    __DIR__ . '/../controllers/UserController.php',
    __DIR__ . '/../controllers/AdminController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("File not found: " . $file);
    }
}
// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Parse request path
$basePath = '/api';
$requestUri = $_SERVER['REQUEST_URI'];
$path = str_replace($basePath, '', $requestUri);
$path = trim($path, '/');
$segments = $path ? explode('/', $path) : [];

error_log("Processed path: " . $path);

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
        echo ErrorMessage::resourceNotFound()->asJson();
        exit();
    }

    switch ($segments[0]) {
        case ApiRoutes::DB_USERS:
            if ($requestMethod === HttpRequestMethod::PUT)
            {
                $jwt = new JwtService();
                $jwt_data = $jwt->getPayload();
                $user = new User($database->getConnection());
                $exists = $user->exists($jwt_data["user_id"]);
                if($exists)
                {
                    $json = file_get_contents("php://input");
                    $data = json_decode($json, true);
                    $user->update($jwt_data["user_id"],
                        [$data["name"], $data["email"], $data["password"]]);
                    echo json_encode(["success"=>true]);
                }
                exit;
            }
            if ($requestMethod === HttpRequestMethod::GET)
            {
                $jwt = new JwtService();
                $jwt_data = $jwt->getPayload();
                $user = new User($database->getConnection());
                $exists = $user->exists($jwt_data["user_id"]);
                if($exists)
                {
                    echo json_encode($user->select_one($jwt_data["user_id"]));
                }
                exit;
            }
            exit;
        case ApiRoutes::LOGIN:
            if ($requestMethod === HttpRequestMethod::POST)
            {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $user = new User($database->getConnection());
                $result = $user->login_default($data['email'], $data['password']);
                $jwt = new JwtService();
                $token = $jwt->encodeUser($result["id"], $result["role_id"]);
                $response = [
                    'success' => true,
                    'token' => $token,
                    "data" =>  [
                        'name' => $result["name"]
                    ],
                    'message' => 'Success'
                ];
                echo json_encode($response);
            }
            exit;
        case ApiRoutes::REGISTER:
            if ($requestMethod === HttpRequestMethod::POST)
            {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $user = new User($database->getConnection());
                $id = $user->sign_up($data["name"], $data['email'], $data['password']);
                $jwt = new JwtService();
                $token = $jwt->encodeUser($id, 4);
                $response = [
                    'success' => true,
                    'token' => $token,
                    'data' =>  [
                        'name' => $data["name"]
                    ],
                    'message' => 'Success'
                ];
                echo json_encode($response);
            }
            exit;
        case ApiRoutes::DB_TICKETS:
            if ($requestMethod === HttpRequestMethod::POST)
            {
                $jwt = new JwtService();
                $jwt_data = $jwt->getPayload();
                $user = new User($database->getConnection());
                $exists = $user->exists($jwt_data["user_id"]);
                if($exists)
                {
                    $json = file_get_contents("php://input");
                    $data = json_decode($json, true);
                    $session = new Session($database->getConnection());
                    $session->buy_places($data['session_id'], $data['seats'], $jwt_data["user_id"]);
                    echo json_encode(["success"=>true]);
                    exit;
                }
                exit;
            }
            if ($requestMethod === HttpRequestMethod::GET)
            {
                $jwt = new JwtService();
                $jwt_data = $jwt->getPayload();
                $user = new User($database->getConnection());
                $exists = $user->exists($jwt_data["user_id"]);
                if($exists)
                {
                    $ticket = new Ticket($database->getConnection());
                    $ticket->select_user($jwt_data["user_id"]);
                    exit;
                }
            }
            exit;
        case ApiRoutes::TICKETS_CHECK:
            if ($requestMethod === HttpRequestMethod::POST)
            {
                $jwt = new JwtService();
                $jwt_data = $jwt->getPayload();
                $user = new User($database->getConnection());
                $exists = $user->exists($jwt_data["user_id"]);
                if($exists)
                {
                    $json = file_get_contents("php://input");
                    $data = json_decode($json, true);
                    $ticket = new Ticket($database->getConnection());
                    $is_valid = $ticket->validate(
                        $jwt_data["user_id"],$data["ticket_id"], $data["row"], $data["col"], $data["session_id"]);
                    if($is_valid) {
                        echo json_encode(["success" => true]);
                    }
                    else{
                        http_response_code(403);
                        echo ErrorMessage::notValidTicket()->asJson();
                    }
                    exit;
                }
            }
            exit;
        case ApiRoutes::DB_MOVIES:
            $controller = new MovieController($db, $requestMethod);
            if (isset($segments[1]) && is_numeric($segments[1])) {
                $controller->setMovieId($segments[1]);
            }
            $controller->processRequest();
            break;

        case ApiRoutes::DB_SESSIONS:
            $controller = new SessionController($db, $requestMethod);
            if (isset($segments[1]) && is_numeric($segments[1])) {
                $controller->setSessionId($segments[1]);
            } elseif (isset($queryParams['movie_id'])) {
                $controller->setMovieId($queryParams['movie_id']);
            }
            $controller->processRequest();
            break;


        case ApiRoutes::IMAGES:
            switch ($segments[1]) {
                case ApiRoutes::DB_DIRECTORS:
                    $path = __DIR__ . "/../images/directors/" . $segments[2] . ".png";
                    if (file_exists($path)) {
                        header('Content-Type: image/png'); // або image/png, якщо PNG
                        header('Content-Length: ' . filesize($path));
                        readfile($path); // Виводимо зображення
                        exit;
                    } else {
                        echo json_encode(["error" => "Image not found"]);
                    }
                    exit();
                case ApiRoutes::DB_ACTORS:
                    $path = __DIR__ . "/../images/actors/" . $segments[2] . ".png";
                    if (file_exists($path)) {
                        header('Content-Type: image/png'); // або image/png, якщо PNG
                        header('Content-Length: ' . filesize($path));
                        readfile($path); // Виводимо зображення
                        exit;
                    } else {
                        echo json_encode(["error" => "Image not found"]);
                    }
                    exit();
                case ApiRoutes::DB_MOVIES:
                    $path = __DIR__ . "/../images/movies/" . $segments[2] . ".png";
                    if (file_exists($path)) {
                        header('Content-Type: image/png'); // або image/png, якщо PNG
                        header('Content-Length: ' . filesize($path));
                        readfile($path); // Виводимо зображення
                        exit;
                    } else {
                        echo json_encode(["error" => "Image not found"]);
                    }
                    exit();
            }
            break;

        case ApiRoutes::ADMIN:
            if ($segments[1] === ApiRoutes::LOGIN) {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $user = new User($database->getConnection());
                $result = $user->login_admin($data['email'], $data['password']);
                if(!$result)
                {
                    http_response_code(400);
                    echo ErrorMessage::userAdminNotFound()->asJson();
                    exit;
                }
                $jwt = new JwtService();
                $token = $jwt->encodeUser($result["id"], $result["role_id"]);
                $response = [
                    'success' => true,
                    'token' => $token,
                    'message' => 'Success'
                ];
                error_log(json_encode($response));
                echo json_encode($response);
            }
            else{
                process_admin($segments, $requestMethod, $database);
            }
            exit;

    }
} catch (Exception $e) {
    error_log('Routing Error: ' . $e->getMessage());
    http_response_code(500);
    echo ErrorMessage::internalServerError()->asJson();
    exit();
}

function process_admin($segments, $requestMethod, $database)
{
    switch ($segments[1]) {
        case ApiRoutes::DB_GENRES:
            $genres = new Genres($database->getConnection());
            if ($requestMethod === HttpRequestMethod::GET) {
                $genres->select();
                exit();
            } else if ($requestMethod === HttpRequestMethod::POST) {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $genres->insert([$data["name"]]);
                exit();
            } else if ($requestMethod === HttpRequestMethod::DELETE) {
                $genres->remove($segments[2]);
                exit();
            } else if ($requestMethod === HttpRequestMethod::PATCH) {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $genres->update($segments[2], [$data["name"]]);
                exit();
            }
            break;
        case ApiRoutes::DB_ACTORS:
            $actors = new Actor($database->getConnection());
            if ($requestMethod === HttpRequestMethod::GET) {
                $actors->select();
                exit();
            } else if ($requestMethod === HttpRequestMethod::POST) {
                if (isset($_POST["PUT"]) and $_POST["PUT"]) {
                    $actors->update($segments[2], [$_POST["name"], $_POST["birthday"]]);
                    exit();
                }
                $actors->insert([$_POST["name"], $_POST["birthday"]]);
                exit();
            } else if ($requestMethod === HttpRequestMethod::DELETE) {
                $actors->remove($segments[2]);
                exit();
            }
            break;
        case ApiRoutes::DB_MOVIES:
            $movies = new Movie($database->getConnection());
            if ($requestMethod === HttpRequestMethod::GET) {
                $movies->select();
                exit();
            } else if ($requestMethod === HttpRequestMethod::POST) {
                if (isset($_POST["PUT"]) and $_POST["PUT"]) {
                    $movies->update($segments[2], [
                        $_POST["title"], $_POST["description"], $_POST["director_id"], $_POST["duration"],
                        $_POST["rating"], $_POST["year"]]);
                    exit();
                }
                $id = $movies->insert([
                    $_POST["title"], $_POST["description"], $_POST["director_id"], $_POST["duration"],
                    $_POST["rating"], $_POST["year"]]);
                $movies->insert_genres($id, $_POST["genres"]);
                $movies->insert_actors($id, json_decode($_POST["actors"], true));
                exit();
            } else if ($requestMethod === HttpRequestMethod::DELETE) {
                $movies->remove($segments[2]);
                exit();
            }
            break;
        case ApiRoutes::DB_HALLS_SHORT:
            $halls = new Halls($database->getConnection());
            $halls->select_ids();
            exit();

        case ApiRoutes::DB_MOVIES_SHORT:
            $movies = new Movie($database->getConnection());
            $movies->select_ids();
            exit();
        case ApiRoutes::DB_HALLS:
            $halls = new Halls($database->getConnection());
            if ($requestMethod === HttpRequestMethod::GET) {
                $halls->select();
                exit();
            } else if ($requestMethod === HttpRequestMethod::POST) {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $halls->insert([$data["id"], $data["seat_map_template"]]);
                exit();
            } else if ($requestMethod === HttpRequestMethod::DELETE) {
                $halls->remove($segments[2]);
                exit();
            } else if ($requestMethod === HttpRequestMethod::PUT) {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $halls->update($segments[2], [$segments[2], $data["seat_map_template"]]);
                exit();
            }
            break;
        case ApiRoutes::DB_SESSIONS:
            $sessions = new Session($database->getConnection());
            if ($requestMethod === HttpRequestMethod::GET) {
                $sessions->select();
                exit();
            } else if ($requestMethod === HttpRequestMethod::POST) {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $id = $sessions->insert([$data["movie_id"], $data["hall_id"], $data["time"]]);
                $sessions->copy_map($id);
                exit();
            } else if ($requestMethod === HttpRequestMethod::DELETE) {
                $sessions->remove($segments[2]);
                exit();
            } else if ($requestMethod === HttpRequestMethod::PUT) {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                $sessions->update($segments[2], [$data["movie_id"], $data["hall_id"], $data["time"]]);
                $sessions->copy_map($segments[2]);
                exit();
            }
            break;
        default:
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Resource not found'
            ]);
            exit();
    }
}