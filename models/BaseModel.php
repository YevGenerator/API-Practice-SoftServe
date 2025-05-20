<?php
abstract class BaseModel
{
    protected $conn;
    public $table_name = "table";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function columns(): array
    {
        return [];
    }

    public function select()
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table_name}");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
    }

    public function select_ids()
    {
        $stmt = $this->conn->prepare("SELECT id FROM {$this->table_name}");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array_column($rows, "id"));
    }

    public function remove($id)
    {
        $stmt = $this->conn->prepare("delete from {$this->table_name} where id=:id");
        $stmt->execute([
            ':id' => $id,
        ]);
        echo json_encode([
            "success" => true
        ]);
    }

    public function insert($values)
    {
        $columns = $this->columns();
        $column_strings = implode('`,`', $columns);
        $placeholders = ":" . implode(", :", $columns);
        $sql = "insert into $this->table_name (`$column_strings`) VALUES ($placeholders)";
        error_log("sql: ". $sql);
        $stmt = $this->conn->prepare($sql);
        for ($i = 0; $i < count($values); $i++) {
            $stmt->bindValue(":$columns[$i]", $values[$i]);
        }
        $stmt->execute();
        echo json_encode([
            "success" => true
        ]);
        return $this->conn->lastInsertId();
    }

    public function update($id, $values)
    {
        $columns = $this->columns();

        $set_clause = implode(', ', array_map(function ($col) {
            return "$col = :$col";
        }, $columns));

        $sql = "UPDATE $this->table_name SET $set_clause WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        for ($i = 0; $i < count($columns); $i++) {
            $stmt->bindValue(":{$columns[$i]}", $values[$i]);
        }
        $stmt->bindValue(":id", $id);
        $stmt->execute();
    }

}