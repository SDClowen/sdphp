<?php
namespace Core;

class Request
{
	/**
	 * Get HTTP Headers
	 *
	 * @param string
	 * @return string|array
	 */
	public static function headers($param = null)
	{
		$headers = getallheaders();

		if (is_null($param))
			return $headers;
		else {
			$headerResponse = [];
			foreach ($headers as $key => $val) {
				$headerResponse[$key] = $val;
			}
			return $headerResponse[ucwords($param)];
		}
	}

	/**
	 * Get Variables
	 *
	 * @param string $param
	 * @return string|array
	 */
	public static function get($param = null, $filter = true)
	{
		if (is_null($param)){
			if($filter)
				return self::filterAll($_GET);
			else
				return $_GET;
		}
		else
			return self::filter($_GET[$param], $filter);
	}

	/**
	 * Post Variables
	 *
	 * @param string $param
	 * @return string|stdClass
	 */
	public static function post($param = null, $filter = true)
	{
		if (is_null($param))
		{
			if($filter)
				return self::filterAll($_POST);
			else
				return $_POST;
		}
		else
			return self::filter($_POST[$param], $filter);
	}

	/**
	 * Put Variables
	 *
	 * @param string $param
	 * @param boolean $filter
	 */
	public static function put($param = null, $filter = true)
	{
		parse_str(file_get_contents("php://input"), $_PUT);

		if ($param == null)
		{
			if($filter)
				return self::filterAll($_PUT);
			else
				return $_PUT;
		}
		else
			return self::filter($_PUT[$param], $filter);
	}

	/**
	 * Delete Variables
	 *
	 * @param string $param
	 * @param boolean $filter
	 */
	public static function delete($param = null, $filter = true)
	{
		parse_str(file_get_contents("php://input"), $_DELETE);

		if ($param == null)
			return $_DELETE;
		else
			return self::filter($_DELETE[$param], $filter);
	}

	/**
	 * Get File Variables Fix
	 *
	 * @param string $param
	 * @return string|object
	 */
	private static function multiFileFix($files = null)
	{
		if ($files == null) {
			$files = (is_array($_FILES)) ? $_FILES : array();
		}

		//make there there is a file, and see if the first item is also an array
		$new_files = array();
		foreach ($files as $name => $attributes) {
			if (is_array(reset($attributes))) { //check first item
				foreach ($attributes as $attribute => $item) { //array file submit, eg name="model[file]"
					foreach ($item as $key => $value) {
						if (is_array($value)) {
							foreach ($value as $key2 => $sub_val) { // multi-array file submit, eg name="model[file][]"
								$new_files[$name][$key][$key2][$attribute] = $sub_val;
							}
						}
						else {
							$new_files[$name][$key][$attribute] = $value;
						}
					}
				}
			}
			else { // regular file submit, eg name="file"
				$new_files[$name] = $attributes;
			}
		}

		return $new_files;
	}

	/**
	 * Get File Variables
	 *
	 * @param string $param
	 * @return string|object
	 */
	public static function files($param = null)
	{
		if (!is_null($param) && isset($_FILES[$param]))
			return $_FILES[$param];

		return json_decode(json_encode(self::multiFileFix($_FILES)));
	}

	/**
	 * Get Globals
	 *
	 * @param string $param
	 * @return string|array
	 */
	public static function globals($param = null)
	{
		if (is_null($param))
			return $GLOBALS;
		else
			return $GLOBALS[$param];
	}

	/**
	 * Get Request Method
	 *
	 * @return string
	 */
	public static function getRequestMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Get Script Name
	 *
	 * @return string
	 */
	public static function getScriptName()
	{
		return $_SERVER['SCRIPT_NAME'];
	}

	/**
	 * Get Request Scheme
	 *
	 * @return string
	 */
	public static function getScheme()
	{
		return stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https' : 'http';
	}

	/**
	 * Get Http Host
	 *
	 * @return string
	 */
	public static function getHost()
	{
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * Get Request URI
	 *
	 * @return string
	 */
	public static function getRequestUri()
	{
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Get Base URL
	 *
	 * @param string $url
	 * @return string
	 */
	public static function baseUrl($url = null)
	{
		if (is_null($url))
			return self::getScheme() . '://' . self::getHost();
		else
			return self::getScheme() . '://' . rtrim(self::getHost(), '/') . '/' . $url;
	}

	/**
	 * Get URL Segments
	 *
	 * @return array
	 */
	public static function segments()
	{
		//return array_filter(explode('/', trim(parse_url(self::getRequestUri(), PHP_URL_PATH), '/')));
		return array_filter(explode('/', trim(self::getRequestUri(), '/')));
	}

	/**
	 * Get specified segment from URL
	 *
	 * @param int $index
	 * @return string
	 */
	public static function getSegment($index = 0)
	{
		return @self::segments()[$index];
	}

	/**
	 * Get current URL Segment
	 *
	 * @return string
	 */
	public static function currentSegment()
	{
		$numSegment = count(self::segments());
		return self::getSegment($numSegment - 1);
	}

	/**
	 * Get Query String Elements
	 *
	 * @param boolean $array (If true then return as an array)
	 * @return string|array
	 */
	public static function getQueryString($array = false)
	{
		if ($array === false) {
			return $_SERVER['QUERY_STRING'];
		}
		else {
			$qsParts = explode('&', $_SERVER['QUERY_STRING']);
			$qsArray = [];

			foreach ($qsParts as $key => $value) {
				$qsItems = explode('=', $value);
				$qsArray[$qsItems[0]] = $qsItems[1];
			}

			return $qsArray;
		}
	}

	/**
	 * Get Content Type
	 *
	 * @return string
	 */
	public static function getContentType()
	{
		return explode(',', self::headers()['Accept'])[0];
	}

	/**
	 * Get Locales
	 *
	 * @return array
	 */
	public static function getLocales()
	{
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			return explode(',', preg_replace('/(;q=[0-9\.]+)/i', '', trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
		else
			return ["en-US"];
	}

	/**
	 * Get the locale
	 *
	 * @return string
	 */
	public static function getLocale($lenght = 5)
	{
		$array = self::getLocales();
		$locales = array_filter($array, fn($value) => strlen($value) == 5);

		return current($locales);
	}

	/**
	 * Check if the requested method is of specified type
	 *
	 * @return string
	 */
	public static function isMethod($method)
	{
		return self::getRequestMethod() === strtoupper($method);
	}

	/**
	 * Check if the request is an ajax request
	 *
	 * @return bool
	 */
	public static function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}

	/**
	 * Check if the http request is secure
	 *
	 * @return bool
	 */
	public static function isSecure()
	{
		if (null !== $_SERVER['https'])
			return true;

		if (null !== $_SERVER['HTTP_X_FORWARDED_PROTO'] && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
			return true;

		return false;
	}

	/**
	 * Check if the visitor is robot
	 *
	 * @return boolean
	 */
	public static function isRobot()
	{
		if (null !== $_SERVER['HTTP_USER_AGENT'] && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']))
			return true;
		else
			return false;
	}

	/**
	 * Check if the visitor is mobile
	 *
	 * @return boolean
	 */
	public static function isMobile()
	{
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}

	/**
	 * Check is referral
	 *
	 * @return boolean
	 */
	public static function isReferral()
	{
		if (null !== $_SERVER['HTTP_REFERER'] || $_SERVER['HTTP_REFERRER'] == '')
			return false;
		else
			return true;
	}

	/**
	 * Return Http Referrer
	 *
	 * @return string
	 */
	public static function getReferrer()
	{
		return (self::isReferral()) ? trim($_SERVER['HTTP_REFERRER']) : '';
	}

	/**
	 * Get client IP
	 *
	 * @return string
	 */
	public static function getIp()
	{
		$ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
		foreach ($ip_keys as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					// trim for safety measures
					$ip = trim($ip);
					// attempt to validate IP

					$validateIp = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
					if (!$validateIp) {
						return strlen($ip) > 20 ? substr($ip, 0, 20) : $ip;
					}
				}
			}
		}
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
	}

	public static function filterAll(array $data) : object
	{
		$pvars = new \stdClass;
		foreach ($data as $key => $value)
			$pvars->{$key} = self::filter($value);
		
		return $pvars;
	}

	/**
	 * Filter inputs
	 *
	 * @param string $data
	 * @param boolean $filter
	 * @return string | null
	 */
	public static function filter($data = null, $filter = true)
	{
	    if ($data === null || !is_string($data)) {
	        return '';
	    }
	
	    $data = trim($data);
	    if (!$filter) {
	        return $data;
	    }
	
	    // Convert special characters to HTML entities first
	    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	
	    // Remove null bytes
	    $data = str_replace("\0", '', $data);
	
	    // Remove UTF-8 characters from 0x00-0x08, 0x0B-0x0C, 0x0E-0x1F
	    $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $data);
	
	    // Remove potentially dangerous strings
	    $dangerous = array(
	        '/\\.\\.\\//', // Directory traversal
	        '/--/',        // SQL comments
	        '/\\<\\/?[a-z]/',  // HTML tags
	        '/javascript:/i',   // JavaScript protocol
	        '/vbscript:/i',    // VBScript protocol
	        '/data:/i',        // Data protocol
	        '/base64/i',       // Base64 encoded content
	        '/eval\\(/i',      // eval()
	        '/expression\\(/i', // CSS expression
	        '/union\\s+select/i', // SQL injection
	        '/exec\\(/i',      // Code execution
	        '/GLOBALS/i',      // PHP globals
	        '/_SERVER/i',      // PHP server
	        '/_GET/i',         // PHP GET
	        '/_POST/i',        // PHP POST
	        '/_FILES/i',       // PHP FILES
	        '/_COOKIE/i',      // PHP COOKIE
	        '/_SESSION/i',     // PHP SESSION
	        '/_ENV/i',         // PHP ENV
	        '/_REQUEST/i',     // PHP REQUEST
	    );
	
	    $data = preg_replace($dangerous, '', $data);
	
	    // Remove any HTML entities that might be malicious
	    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	
	    // Remove all remaining HTML/XML tags and attributes
	    $data = strip_tags($data);
	    return $data;
	    // Double encode to prevent bypass attempts
	    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
	}
}
?>
