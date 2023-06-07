<?php

	function session_get($key)
	{
		return @$_SESSION[$key];
	}

	function session_set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	function session_restart_with($key, $value)
	{
        session_destroy();
        session_start();
        
		$_SESSION[$key] = $value;
	}

	function session_remove_key($key)
	{
		unset($_SESSION[$key]);
	}
	
	function session_check($name)
	{
        if(session_status() == 0)
            return false;

		return isset($_SESSION[$name]);
	}

	function session_remove($key)
	{
		unset($_SESSION[$key]);
	}