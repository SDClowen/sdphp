<?php 
namespace Core;

class StaticEntity
{
	public static function __callStatic(string $method, array $parameters)
	{
		$vars = get_class_vars(get_called_class());
		if(!isset($vars["methods"]))
			throw new \Exception("You must add methods to the called class!");
		
		$methods = $vars["methods"];

		if (!array_key_exists($method, $methods)) {
			throw new \Exception('The ' . $method . ' is not supported.');
		}

		return call_user_func_array($methods[$method], $parameters);
	}
}

/*
	Str::upper("sdclowen")
*/
class Str extends StaticEntity{
	protected static $methods = [
		'upper' => 'strtoupper',
		'lower' => 'strtolower',
		'len' => 'strlen'
	];
}
?>