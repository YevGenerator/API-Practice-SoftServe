<?php
require_once "BaseModel.php";
require_once __DIR__ . "/db_constants/TableNames.php";

class User extends BaseModel
{

    public function __construct($db)
    {
        parent::__construct($db);
        $this->table_name = TableNames::USERS;
    }

    public function exists($id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM $this->table_name WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    public function login($email, $password, $role_id)
    {
        $query = "SELECT id, role_id, name FROM {$this->table_name} WHERE 
                                  email = :email AND 
                                  password = :password AND 
                                  role_id = :role_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':email' => $email,
            ':password' => $password,
            ':role_id' => $role_id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login_default($email, $password)
    {
        return $this->login($email, $password, 4);
    }
    public function login_admin($email, $password)
    {
        return $this->login($email, $password, 3);
    }

    public function sign_up($name, $email, $password)
    {
        $query = "
insert into {$this->table_name} (name, email, password, role_id)
values (:name, :email, :password, :role_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password,
            ':role_id' => 4
        ]);
        return $this->conn->lastInsertId();
    }

} 