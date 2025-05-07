<?php

class MovieController extends BaseController {
    private $movie;
    private $movieId;

    public function __construct($db, $requestMethod) {
        parent::__construct($db, $requestMethod);
        $this->movie = new Movie($db);
    }

    public function setMovieId($movieId) {
        $this->movieId = $movieId;
    }

    public function processRequest() {
        try {
            switch ($this->requestMethod) {
                case 'GET':
                    if (isset($this->movieId)) {
                        $this->getMovie($this->movieId);
                    } else {
                        $this->getAllMovies();
                    }
                    break;
                case 'POST':
                    $this->createMovie();
                    break;
                case 'PUT':
                    if (isset($this->movieId)) {
                        $this->updateMovie($this->movieId);
                    } else {
                        $this->setResponse(400, null, 'Movie ID is required');
                    }
                    break;
                case 'DELETE':
                    if (isset($this->movieId)) {
                        $this->deleteMovie($this->movieId);
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
        exit();
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
                'posterUrl' => $row['poster_url'],
                'genre' => $row['genre'] ?? '',
                'year' => (int)($row['year'] ?? 0),
                'rating' => (float)($row['rating'] ?? 0),
                'actors' => $row['actors'] ?? ''
            ];
        }

        if (empty($movies)) {
            $this->setResponse(404, null, 'No movies found');
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
            'posterUrl' => $this->movie->poster_url,
            'genre' => $this->movie->genre,
            'year' => $this->movie->year,
            'rating' => $this->movie->rating,
            'actors' => $this->movie->actors
        ];

        $this->setResponse(200, $movieData);
    }
    
    private function createMovie() {
        // Check if user has admin rights
        // Implement your authorization logic here
        
        // Get request body
        $data = json_decode(file_get_contents("php://input"));
        
        if (
            empty($data->title) || 
            empty($data->description) || 
            empty($data->duration)
        ) {
            $this->setResponse(400, null, 'Incomplete data. Title, description, and duration are required.');
            return;
        }
        
        // Set movie properties
        $this->movie->title = $data->title;
        $this->movie->description = $data->description;
        $this->movie->duration = $data->duration;
        $this->movie->poster_url = $data->poster_url ?? '';
        $this->movie->genre = $data->genre ?? '';
        $this->movie->year = $data->year ?? null;
        $this->movie->rating = $data->rating ?? null;
        $this->movie->actors = $data->actors ?? '';
        
        if ($this->movie->create()) {
            $this->setResponse(201, ['id' => $this->movie->id, 'message' => 'Movie created']);
        } else {
            $this->setResponse(500, null, 'Failed to create movie');
        }
    }
    
    private function updateMovie($id) {
        // Check if user has admin rights
        // Implement your authorization logic here
        
        if (!is_numeric($id)) {
            $this->setResponse(400, null, 'Invalid movie ID');
            return;
        }
        
        $this->movie->id = (int)$id;
        
        // Check if movie exists
        if (!$this->movie->readOne()) {
            $this->setResponse(404, null, 'Movie not found');
            return;
        }
        
        // Get request body
        $data = json_decode(file_get_contents("php://input"));
        
        // Update movie properties
        $this->movie->title = $data->title ?? $this->movie->title;
        $this->movie->description = $data->description ?? $this->movie->description;
        $this->movie->duration = $data->duration ?? $this->movie->duration;
        $this->movie->poster_url = $data->poster_url ?? $this->movie->poster_url;
        $this->movie->genre = $data->genre ?? $this->movie->genre;
        $this->movie->year = $data->year ?? $this->movie->year;
        $this->movie->rating = $data->rating ?? $this->movie->rating;
        $this->movie->actors = $data->actors ?? $this->movie->actors;
        
        if ($this->movie->update()) {
            $this->setResponse(200, ['message' => 'Movie updated']);
        } else {
            $this->setResponse(500, null, 'Failed to update movie');
        }
    }
    
    private function deleteMovie($id) {
        // Check if user has admin rights
        // Implement your authorization logic here
        
        if (!is_numeric($id)) {
            $this->setResponse(400, null, 'Invalid movie ID');
            return;
        }
        
        $this->movie->id = (int)$id;
        
        if ($this->movie->delete()) {
            $this->setResponse(200, ['message' => 'Movie deleted']);
        } else {
            $this->setResponse(500, null, 'Failed to delete movie');
        }
    }
} 