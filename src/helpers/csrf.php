<?php

	function generate_csrf()
	{
		$value = gen_pw(32);

		$_SESSION["csrf_value"] = $value;
		$_SESSION["csrf_hash"] = md5(sha1(md5("csrf_generated_token_value:".$value)));

		return $value;
	}

	function check_csrf($value)
	{
		return isset($_SESSION["csrf_value"]) && 
				isset($_SESSION["csrf_hash"]) &&
				md5(sha1(md5("csrf_generated_token_value:".$value))) === $_SESSION["csrf_hash"];
	}

	function csrf()
	{
		return "<input type='hidden' value='".generate_csrf()."'/>";
	}