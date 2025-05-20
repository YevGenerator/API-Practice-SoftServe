<?php
require_once 'BaseModel.php';
class Actor extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db);
        $this->table_name = 'actors';
    }
    public function columns(): array
    {
        return ["name", "birthday"];
    }

    public function insert($values)
    {
        $id = parent::insert($values);
        $path = __DIR__."/../images/actors/".$id;
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
            http_response_code(400);
            echo json_encode(['error' => 'Файл не надіслано']);
        }
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
        $path = __DIR__."/../images/actors/".$id;
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