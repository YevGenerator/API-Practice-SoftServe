<?php
require_once "BaseModel.php";
require_once "Ticket.php";
class Session extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db);
        $this->table_name = "sessions";
    }

    public function columns(): array
    {
        return ["movie_id", "hall_id", "time"];
    }

    public function copy_map($session_id)
    {
        $stmt = $this->conn->prepare("
UPDATE $this->table_name s
    JOIN halls h ON s.hall_id = h.id
    SET s.seat_map = h.seat_map_template
    WHERE s.id = :session_id
");
        $stmt->execute([':session_id' => $session_id]);
    }

    function get_map($session_id)
    {
        error_log("session_id ".$session_id);
        $stmt = $this->conn->prepare("SELECT seat_map FROM $this->table_name WHERE id = :id");
        $stmt->execute([':id' => $session_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['seat_map'] : null;
    }

    function update_map($session_id, $map)
    {
        $stmt = $this->conn->prepare("UPDATE $this->table_name SET seat_map = :seat_map WHERE id = :id");
        $stmt->execute([
            ':id' => $session_id,
            ':seat_map' => $map,
        ]);
    }

    function buy_places($session_id, $seats, $user_id)
    {
        $ticket = new Ticket($this->conn);
        $prices = [
            "a" => 100,
            "b" => 150
        ];
        $places = ['a', 'b', 'x'];
        $map = $this->get_map($session_id);
        $lines = explode("\n", $map);
        $lineCount = count($lines);
        foreach ($seats as $seat) {
            $targetRow = $lineCount - $seat['row'];
            $targetCol = $seat['col'];

            if (!isset($lines[$targetRow])) continue;

            $line = $lines[$targetRow];
            $chars = mb_str_split($line);
            $currentCol = 0;

            for ($i = 0; $i < count($chars); $i++) {
                if (in_array($chars[$i], $places)) {
                    $currentCol++;
                    if ($currentCol === $targetCol) {
                        $ticket->insert([$session_id, $targetRow, $targetCol, $prices[$chars[$i]], $user_id]);
                        $chars[$i] = 'x';
                        break;
                    }
                }
            }

            $lines[$targetRow] = implode('', $chars);
        }
        $map= implode("\n", $lines);
        $this->update_map($session_id, $map);
    }

}