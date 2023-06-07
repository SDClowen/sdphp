<?php

defined("DIRECT") or exit("No direct script access allowed");

class Model
{
	public Database $db;

	public function __construct()
	{
		$this->db = Database::get();
	}

	public static function new() : Model
    {
        $called = get_called_class();

        return new $called;
    }
}

?>