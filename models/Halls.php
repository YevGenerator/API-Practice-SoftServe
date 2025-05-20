<?php
require_once "BaseModel.php";
class Halls extends BaseModel
{
    public function columns(): array
    {
        return ["id", "seat_map_template"];
    }
    public function __construct($db)
    {
        parent::__construct($db);
        $this->table_name = "halls";
    }

}