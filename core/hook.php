<?php 

defined("DIRECT") or exit("No direct script access allowed");

class Hook
{
	private static $scriptHooks = [];

	public static function set_script($script)
	{
		self::$scriptHooks[] = $script;
	}

	public static function get_scripts()
	{
		return self::$scriptHooks;
	}

	public static function print_scripts()
	{
		if ($scripts = self::get_scripts()) {
			echo "<script> $(function() {";
			foreach ($scripts as $value)
				echo $value;

			echo "}); </script>";
		}
	}

	public static function importCss($path)
	{
		echo '<link href="'.$path.'" rel="stylesheet"/>';
	}

	public static function includeJs($path)
	{
		
	}
}
?>