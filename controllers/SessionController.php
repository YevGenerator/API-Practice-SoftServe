<?php

class SessionController extends BaseController {
    private $session;
    private $movieId;
    private $sessionId;

    public function __construct($db, $requestMethod) {
        parent::__construct($db, $requestMethod);
        $this->session = new Session($db);
    }

    public function setMovieId($movieId) {
        $this->movieId = $movieId;
    }

    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    public function processRequest() {
        try {
            switch ($this->requestMethod) {
                case 'GET':
                    if (isset($this->sessionId)) {
                        $this->getSession($this->sessionId);
                    } elseif (isset($this->movieId)) {
                        $this->getMovieSessions($this->movieId);
                    } else {
                        $this->getAllSessions();
                    }
                    break;
                default:
                    $this->setResponse(405, null, 'Method not allowed');
                    break;
            }
        } catch (Exception $e) {
            error_log('SessionController Error: ' . $e->getMessage());
            $this->setResponse(500, null, 'Internal server error');
        }
        
        $this->sendResponse();
        exit();
    }

    private function getAllSessions() {
        $result = $this->session->read();
        $sessions = [];
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $sessions[] = [
                'id' => (int)$row['id'],
                'movieId' => (int)$row['movie_id'],
                'startTime' => $row['start_time'],
                'hall' => $row['hall'],
                'price' => (float)$row['price']
            ];
        }

        if (empty($sessions)) {
            $this->setResponse(404, null, 'No sessions found');
        } else {
            $this->setResponse(200, $sessions);
        }
    }

    private function getSession($id) {
        if (!is_numeric($id)) {
            $this->setResponse(400, null, 'Invalid session ID');
            return;
        }

        $this->session->id = (int)$id;
        
        if (!$this->session->readOne()) {
            $this->setResponse(404, null, 'Session not found');
            return;
        }

        $sessionData = [
            'id' => $this->session->id,
            'movieId' => $this->session->movie_id,
            'startTime' => $this->session->start_time,
            'hall' => $this->session->hall,
            'price' => (float)$this->session->price
        ];

        $this->setResponse(200, $sessionData);
    }

    private function getMovieSessions($movieId) {
        if (!is_numeric($movieId)) {
            $this->setResponse(400, null, 'Invalid movie ID');
            return;
        }

        error_log("Searching for sessions with movie_id: " . $movieId);
        
        $result = $this->session->readByMovie((int)$movieId);
        $sessions = [];
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            error_log("Found session: " . print_r($row, true));
            $sessions[] = [
                'id' => (int)$row['id'],
                'movieId' => (int)$row['movie_id'],
                'startTime' => $row['start_time'],
                'hall' => $row['hall'],
                'price' => (float)$row['price']
            ];
        }

        error_log("Total sessions found: " . count($sessions));

        if (empty($sessions)) {
            $this->setResponse(404, null, 'No sessions found for this movie');
        } else {
            $this->setResponse(200, $sessions);
        }
    }
} 