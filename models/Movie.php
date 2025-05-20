<?php
require_once "BaseModel.php";
class Movie extends BaseModel
{
    public $bind_actors = "movies_actors";
    public $bind_genres = "movie_genres";
    public function __construct($db)
    {
        parent::__construct($db);
        $this->table_name = 'movies';
    }
    public function columns(): array
    {
        return ["title", "description", "director_id", "duration", "rating", "year"];
    }

    public function select_ids()
    {
        $stmt = $this->conn->prepare("SELECT id, title FROM {$this->table_name}");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
    }

    public function select()
    {
        $sql = "
SELECT 
    m.id AS id,
    m.title,
    m.description,
    m.duration,
    m.rating,
    m.year,
    JSON_OBJECT('id', d.id, 'name', d.name) AS director,
    
    -- Жанри через підзапит
    COALESCE(
        (
            SELECT JSON_ARRAYAGG(JSON_OBJECT('id', g.id, 'name', g.name))
            FROM movie_genres mg
            JOIN genres g ON mg.genre_id = g.id
            WHERE mg.movie_id = m.id
        ),
        JSON_ARRAY()
    ) AS genres,

    -- Актори через підзапит
    COALESCE(
        (
            SELECT JSON_ARRAYAGG(JSON_OBJECT(
                'id', a.id,
                'name', a.name,
                'birthday', a.birthday,
                'char_name', ma.char_name
            ))
            FROM movies_actors ma
            JOIN actors a ON ma.actor_id = a.id
            WHERE ma.movie_id = m.id
        ),
        JSON_ARRAY()
    ) AS actors

FROM movies m
LEFT JOIN actors d ON m.director_id = d.id
ORDER BY m.id

";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $movies = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $movies[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'director' => json_decode($row['director']),
                'duration' => $row['duration'],
                'rating' => $row['rating'],
                'year'=>$row["year"],
                'genres' => json_decode($row['genres']) ?? [],
                'actors' => json_decode($row['actors']) ?? []
            ];
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($movies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function insert_genres($movie_id, $genres_id_list)
    {
        $column_strings = "movie_id, genre_id";
        $replace_strings = ":movie_id, :genre_id";
        $sql = "insert into $this->bind_genres ($column_strings) values ($replace_strings)";
        $stmt = $this->conn->prepare($sql);
        foreach ($genres_id_list as $genre_id) {
            $stmt->execute([
                ':movie_id' => $movie_id,
                ':genre_id' => $genre_id,
            ]);
        }
    }
    public function insert_actors($movie_id, $actors_list)
    {
        $column_strings = "movie_id, actor_id, char_name";
        $replace_strings = ":movie_id, :actor_id, :char_name";
        $sql = "INSERT INTO $this->bind_actors ($column_strings) VALUES ($replace_strings)";
        $stmt = $this->conn->prepare($sql);

        foreach ($actors_list as $actor) {
            $stmt->execute([
                ':movie_id' => $movie_id,
                ':actor_id' => $actor['id'],
                ':char_name' => $actor['char_name'],
            ]);
        }
    }
    public function remove_old_actors($movie_id)
    {
        $sql = "delete from $this->bind_actors where movie_id=:movie_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':movie_id' => $movie_id
        ]);
    }
    public function remove_old_genres($movie_id)
    {
        $sql = "delete from $this->bind_genres where movie_id=:movie_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':movie_id' => $movie_id
        ]);
    }
    public function insert($values)
    {
        $id = parent::insert($values);
        $path = __DIR__."/../images/movies/".$id;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageTmpPath = $_FILES['image']['tmp_name'];
            $imageName = basename($_FILES['image']['name']);
            $extension = pathinfo($imageName, PATHINFO_EXTENSION);
            $path = $path . '.' . strtolower($extension);

            if (move_uploaded_file($imageTmpPath, $path)) {
                http_response_code(200);
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Помилка збереження файлу']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Файл не надіслано']);
        }
        return $id;
    }

    public function remove($id)
    {
        parent::remove($id);
        $path = __DIR__."/../images/actors/";
        $files = glob($path . "$id*", GLOB_BRACE);

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    public function update($id, $values){
        parent::update($id, $values);
        $this->remove_old_actors($id);
        $this->remove_old_genres($id);
        $this->insert_actors($id,  json_decode($_POST["actors"], true));
        $this->insert_genres($id,  $_POST["genres"]);
        $path = __DIR__."/../images/movies/".$id;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageTmpPath = $_FILES['image']['tmp_name'];
            $imageName = basename($_FILES['image']['name']);
            $extension = pathinfo($imageName, PATHINFO_EXTENSION);
            $path = $path . '.' . strtolower($extension);

            if (move_uploaded_file($imageTmpPath, $path)) {
                http_response_code(200);
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Помилка збереження файлу']);
            }
            exit;
        } else {
            echo json_encode(['error' => 'Файл не надіслано']);
        }
    }
} 