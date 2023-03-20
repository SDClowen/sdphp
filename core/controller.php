<?php 

defined("DIRECT") or exit("No direct script access allowed");

class Controller
{
	/*
	*  Ran method attributes 
	*/
	public $attributes;
	
	/*
	*  The database instance
	*/
	public $db;

	/*
	* Create new instance of this class
	*/
	public static function new()
	{
		return new self();
	}

	/**
     * Render a view file
     *
     * @param string $view The view file
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @return void 
     */
    public function render($view, array $args = [], $isEcho = true)
    {
        if (file_exists($file = VDIR."/".strtolower($view).".php")) 
        {
            extract($args, EXTR_SKIP);
            ob_start();

            if (is_readable($file))
                require $file;
            else
                exit("Could not found the view file: $view");

            $result = ob_get_clean();
			if($isEcho)
				echo $result;
			else
				return $result;
        } 
        else
            exit("Could not found the view file: $view");
    }
}
?>