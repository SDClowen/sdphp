<?php

	function redirect($url = "/404", $time = 0)
	{
		$time *= 1000;
		die("<script>setTimeout(function(){ window.location.href = '$url' }, $time);</script>");
	}
    
    function http_json($httpLink)
    {
        return json_decode(file_get_contents($httpLink));
	}
	
	function getActiveVisiters()
	{
		return count(glob(session_save_path() . '*'));
	}
	
    function to_float($str)
    {
        if (strlen($str) == 0)
            return 0;
       
        $str = str_replace(" ", "", $str);
       
        if (substr_count($str, ".") > 0)
            $str = str_replace(".", "", $str);
       
        if (substr_count($str, ",") > 0)
            $str = str_replace(",", ".", $str);
       
       if(!is_numeric($str))
           return "NaN";
       
       return floatval($str);
    }

	function is_boolean($value)
	{
		return $value >= 0 && $value <= 1;
	}
    
	function gen_guid()
	{
		return strtoupper(sprintf('%04x-%04x-%04x-%04x-%04x',
						  // 32 bits for "time_low"
						  mt_rand(0, 0xffff), mt_rand(0, 0xffff),
						  // 16 bits for "time_mid"
						  mt_rand(0, 0xffff),
						  // 16 bits for "time_hi_and_version",
						  // four most significant bits holds version number 4
						  mt_rand(0, 0x0fff) | 0x4000,
						  // 16 bits, 8 bits for "clk_seq_hi_res",
						  // 8 bits for "clk_seq_low",
						  // two most significant bits holds zero and one for variant DCE1.1
						  mt_rand(0, 0x3fff) | 0x8000
						));
	}
	
	function gen_pw($len = 16) {
		return substr(md5(base_convert(uniqid(mt_rand(), false), 16, 36)), 0, $len);
	}

	function check_url_segments_security($source)
	{
		foreach ($source as $key => $value)
			if(!alpha_space2($key) || !alpha_space2($value))
				return false;

		return true;
	}

    function lang($key)
    {
        if (array_key_exists($key, $GLOBALS["APP_LANGUAGE"]))
            return vsprintf($GLOBALS["APP_LANGUAGE"][$key], array_slice(func_get_args(), 1));
    }
?>