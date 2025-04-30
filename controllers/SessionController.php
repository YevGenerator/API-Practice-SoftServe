<?php

class SessionController extends BaseController {
    private $session;

    public function __construct($db, $requestMethod) {
        parent::__construct($db, $requestMethod);
        $this->session = new Session($db);
    }

    public function processRequest() {
        try {
            switch ($this->requestMethod) {
                case 'GET':
                    if (isset($_GET['movie_id'])) {
                        $this->getMovieSessions($_GET['movie_id']);
                    } else {
                        $this->setResponse(400, null, 'Movie ID is required');
                    }
                    break;
                default:
                    $this->setResponse(405, null, 'Method not allowed');
                    break;
            }
        } catch (Exception $e) {
            $this->setResponse(500, null, 'Internal server error');
            error_log($e->getMessage());
        }
        
        $this->sendResponse();
    }

    private function getMovieSessions($movieId) {
        if (!is_numeric($movieId)) {
            $this->setResponse(400, null, 'Invalid movie ID');
            return;
        }

        $result = $this->session->readByMovie((int)$movieId);
        $sessions = [];
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $sessions[] = [
                'sessionId' => (int)$row['id'],
                'startTime' => $row['start_time'],
                'hall' => $row['hall'],
                'price' => (float)$row['price']
            ];
        }

        if (empty($sessions)) {
            $this->setResponse(404, null, 'No sessions found for this movie');
            return;
        }

        $this->setResponse(200, $sessions);
    }
} 