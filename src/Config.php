<?php 
namespace Core;

class Config
{
	private $_data;
	private static $_instance;

	public static function get()
	{
		if (null === self::$_instance) {
			$config = Database::get()->from("config")->results();
			$newStd = new \stdClass;
			foreach($config as $value)
			{
				$newStd->{$value->name} = $value->value;
			}
			self::$_instance = new self($newStd);
		}

		return self::$_instance;
	}

	public function __construct($dataObj)
	{
		$this->_data = $dataObj;
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
		$db = Database::get();
		$data = (array)$this->_data;
		foreach($data as $k => $v)
			$db->from("config")->where("name", "=", $k)->update(["value" => $v]);
	}
}
?>