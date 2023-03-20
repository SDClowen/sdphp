<?php

defined("DIRECT") or exit("No direct script access allowed");

class Model
{
	public $db;

	public function __construct()
	{
		$this->db = Database::instance();
	}

	public static function new()
    {
        $called = get_called_class();

        return new $called;
    }
}

?>