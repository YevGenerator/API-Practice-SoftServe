<?php
require_once "BaseModel.php";
class Ticket extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db);
        $this->table_name = "tickets";
    }
    public function columns(): array
    {
        return ["session_id", "row", "col", "price", "user_id"];
    }

    public function validate($user_id, $ticket_id, $row, $col, $session_id)
    {
        $sql = "select count(*) from $this->table_name where
                    user_id = :user_id and
                    id = :ticket_id and
                    `row` = :row and
                    `col` = :col and
                    `session_id` = :session_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":user_id" => $user_id,
            ":ticket_id" => $ticket_id,
            ":row" => $row,
            ":col" => $col,
            ":session_id" => $session_id
        ]);
        return $stmt->fetchColumn() > 0;
    }
    public function select_user($user_id)
    {
        $sql = "
SELECT 
    t.id as ticket_id,
    t.session_id as session_id,
    t.row as `row`,
    t.col as `col`,
    t.buy_time as `buy_time`,
    t.price as `price`,
    s.movie_id as movie_id,
    s.hall_id as hall_id,
    s.time as time,
    m.title AS movie_title,
    u.name AS username
FROM tickets t
    JOIN sessions s ON t.session_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN users u ON t.user_id = u.id
WHERE t.user_id = :user_id
ORDER BY t.buy_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
    }

}