<?php 
class Db extends Database
{
	public static function get()
	{
		return self::instance();
	}
}