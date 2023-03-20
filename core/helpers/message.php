<?php

    function message($message, $type = false, $redirect = false, $scrollTo = false)
    {
        return json_encode(["type" => $type, "message" => $message, "redirect" => $redirect, "scrollTo" => $scrollTo], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
	function session_flush($type, $message, $redirect = "")
	{
		session_set("flush", message($message, $type));
		
		if($redirect)
			header("location: $redirect");
	}
    
    function print_flush_message()
    {
        if (session_check("flush")) 
        {
            $flush = json_decode(session_get("flush"));

            echo '<div class="alert alert-'.$flush->type.'">'.$flush->message.'</div>';

            session_remove_key("flush");
        }
    }

    function error($message = '', $redirect = false, $scrollTo = false)
    {
        die(message($message, "danger", $redirect, $scrollTo));
    }

    function warning($message = '', $redirect = false, $scrollTo = false)
    {
        die(message($message, "warning", $redirect, $scrollTo));
    }

    function success($message = '', $redirect = false, $scrollTo = false)
    {
        die(message($message, "success", $redirect, $scrollTo));
    }

    function info($message = '', $redirect = false, $scrollTo = false)
    {
        die(message($message, "info", $redirect, $scrollTo));
    }

    function console($message)
    {
        if(Request::isAjax())
            return;

        $message = (is_array($message) ? json_encode($message) : $message);
        echo "<script>console.log('$message')</script>\n";
    }
    
    function critical(string $msg)
	{
        echo "<br><div style='color: gray; margin: 20px; padding: 10px;border: 1px solid #999; box-shadow: 0 0 10px #ccc'>";
        debug_print_backtrace();
		die("<br><b><font color=red>$msg</font></b></div>");
	}
    
    function debug(string $msg)
	{
        if(!DEBUG)
            redirect();

        critical($msg);
	}
?>