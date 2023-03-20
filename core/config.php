<?php 
class Config
{
	private $_data;
	private static $_instance;

	public static function get()
	{
		if (null === self::$_instance) {
			$config = Database::instance()->from("config")->first();
			self::$_instance = new self($config);
		}

		return self::$_instance;
	}

	public function __construct(string $json)
	{
		$obj = json_decode($json) or die(json_last_error_msg());
		$this->_data = $obj;
	}

	public function __set($property, $value)
	{
		return $this->_data->$property = $value;
	}

	public function __get($property)
	{
		return $this->_data->$property;
	}

	public function update()
	{
		return Database::instance()->from("config")->update(["json" => json_encode($this->_data)]);
	}
}
?>