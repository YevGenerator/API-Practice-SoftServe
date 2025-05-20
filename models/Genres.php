<?php
require_once "BaseModel.php";
class Genres extends BaseModel
{
    public function columns(): array
    {
        return ["name"];
    }

    public function __construct($db)
    {
        parent::__construct($db);
        $this->table_name = "genres";
    }

}