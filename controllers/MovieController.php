<?php

class MovieController extends BaseController {
    private $movie;

    public function __construct($db, $requestMethod) {
        parent::__construct($db, $requestMethod);
        $this->movie = new Movie($db);
    }

    public function processRequest() {
        try {
            switch ($this->requestMethod) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $this->getMovie($_GET['id']);
                    } else {
                        $this->getAllMovies();
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

    private function getAllMovies() {
        $result = $this->movie->read();
        $movies = [];
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $movies[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'duration' => (int)$row['duration'],
                'posterUrl' => $row['poster_url']
            ];
        }

        if (empty($movies)) {
            $this->setResponse(200, [], 'No movies found');
        } else {
            $this->setResponse(200, $movies);
        }
    }

    private function getMovie($id) {
        if (!is_numeric($id)) {
            $this->setResponse(400, null, 'Invalid movie ID');
            return;
        }

        $this->movie->id = (int)$id;
        
        if (!$this->movie->readOne()) {
            $this->setResponse(404, null, 'Movie not found');
            return;
        }

        $movieData = [
            'id' => $this->movie->id,
            'title' => $this->movie->title,
            'description' => $this->movie->description,
            'duration' => $this->movie->duration,
            'posterUrl' => $this->movie->poster_url
        ];

        $this->setResponse(200, $movieData);
    }
} 