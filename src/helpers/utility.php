<?php

/**
 * Load javascript asset file from public
 */
function ajs($path)
{
	return "<script src='/app/public/js/$path'></script>";
}

/**
 * Load css asset file from public
 */
function acss($path)
{
	return "<link type='text/css' href='/app/public/css/$path' rel='stylesheet'>";
}

/**
 * Load javascript asset file from node modules
 */
function njs($path)
{
	return "<script src='/node_modules/$path'></script>";
}

/**
 * Load css asset file from node modules
 */
function ncss($path)
{
	return "<link type='text/css' href='/node_modules/$path' rel='stylesheet'>";
}

function langShort()
{
	global $cookie;
	$lang = $cookie->get("lang");
	if(empty($lang))
		return "tr"; # default

	return strtolower(substr($lang, 0, 2));
}

function contentDirection()
{
	switch (langShort()) {
		case "ar":
		case "fa":
		case "ur":
			return "rtl";
		default:
			return "ltr";
	}
}

function renderKeywords($keywords, $link = "keywords", $delimiter = ",")
{
	if (! is_array($keywords))
		$keywords = explode($delimiter, $keywords);

	$render = "";
	foreach ($keywords as $keyword)
		$render .= "<a class='ms-2 fw-bold' href='/$link/$keyword'>$keyword</a>$delimiter";

	if (! $render)
		return false;

	return substr($render, 0, strlen($render) - strlen($delimiter));
}

function shortStr($text, $limit, $extend = "...")
{
	if (mb_strlen($text) > $limit)
		return mb_substr($text, 0, $limit) . $extend;

	return $text;
}

function shortText($content, $limit = 20, $more = null, $moreUrl = null)
{
	$content = explode(' ', preg_replace('/\s+/', ' ', strip_tags($content)));

	$nullCount = 0;
	while ($nullCount == 0) {
		if (in_array(null, $content)) {
			unset($content[array_search(null, $content)]);
		} else {
			$nullCount = 1;
		}
	}

	$content = array_values($content);

	if (isset($content[$limit - 1])) {
		for ($i = 0; $i <= ($limit - 1); $i++) {
			$newContent[$i] = $content[$i];
		}

		$content = implode(' ', $newContent);

		if ($more) {
			$content .= ' ';

			if (is_array($moreUrl)) {

				foreach ($moreUrl as $key => $val) {
					$attrs[] = $key . '="' . $val . '"';
				}

				$attr = implode(' ', $attrs);
			} else {
				$attr = 'href="' . $moreUrl . '" title="' . $more . '"';
			}

			$content .= ! $moreUrl ? null : '<a ' . $attr . '>';
			$content .= $more;
			$content .= ! $moreUrl ? null : '</a>';

		}

	} else {
		$content = implode(' ', $content);
	}

	return $content;
}

function _e($str)
{
	static $from = [
		"Ãœ", "Å", "Ä", "Ã‡", "Ä°", "Ã–", "Ã¼", "ÅŸ", "Ã§", "Ä±", "Ã¶", "ÄŸ",
		"Ü", "Ş", "Ğ", "Ç", "İ", "Ö", "ü", "ş", "ç", "ı", "ö", "ğ",
		"%u015F", "%E7", "%FC", "%u0131", "%F6", "%u015E", "%C7", "%DC", "%D6",
		"%u0130", "%u011F", "%u011E"
	];

	static $to = [
		'U', "S", "G", "C", "I", "O", "u", "s", "c", "i", "o", "g",
		"U", "S", "G", "C", "I", "O", "u", "s", "c", "i", "o", "g",
		"s", "c", "u", "i", "o", "S", "C", "U", "O", "I", "g", "G"
	];

	return str_replace($from, $to, $str);
}

function get_ip()
{
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if (isset($_SERVER['REMOTE_ADDR']))
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}

function get_os()
{

	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$os_platform = "Unknown OS Platform";
	$os_array = array(
		'/windows nt 10/i' => 'Windows 10',
		'/windows nt 6.3/i' => 'Windows 8.1',
		'/windows nt 6.2/i' => 'Windows 8',
		'/windows nt 6.1/i' => 'Windows 7',
		'/windows nt 6.0/i' => 'Windows Vista',
		'/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
		'/windows nt 5.1/i' => 'Windows XP',
		'/windows xp/i' => 'Windows XP',
		'/windows nt 5.0/i' => 'Windows 2000',
		'/windows me/i' => 'Windows ME',
		'/win98/i' => 'Windows 98',
		'/win95/i' => 'Windows 95',
		'/win16/i' => 'Windows 3.11',
		'/macintosh|mac os x/i' => 'Mac OS X',
		'/mac_powerpc/i' => 'Mac OS 9',
		'/linux/i' => 'Linux',
		'/ubuntu/i' => 'Ubuntu',
		'/iphone/i' => 'iPhone',
		'/ipod/i' => 'iPod',
		'/ipad/i' => 'iPad',
		'/android/i' => 'Android',
		'/blackberry/i' => 'BlackBerry',
		'/webos/i' => 'Mobile'
	);

	foreach ($os_array as $regex => $value) {
		if (preg_match($regex, $user_agent)) {
			$os_platform = $value;
		}
	}
	return $os_platform;
}

function get_browser_name()
{

	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	$browser = "Unknown Browser";

	$browser_array = array(
		'/msie/i' => 'Internet Explorer',
		'/Trident/i' => 'Internet Explorer',
		'/firefox/i' => 'Firefox',
		'/safari/i' => 'Safari',
		'/chrome/i' => 'Chrome',
		'/edge/i' => 'Edge',
		'/opera/i' => 'Opera',
		'/netscape/i' => 'Netscape',
		'/maxthon/i' => 'Maxthon',
		'/konqueror/i' => 'Konqueror',
		'/ubrowser/i' => 'UC Browser',
		'/mobile/i' => 'Handheld Browser'
	);

	foreach ($browser_array as $regex => $value) {

		if (preg_match($regex, $user_agent)) {
			$browser = $value;
		}

	}

	return $browser;

}

function get_device()
{
	$agent = $_SERVER['HTTP_USER_AGENT'];
	$tablet_browser = 0;
	$mobile_browser = 0;

	if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
		$tablet_browser++;
	}

	if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
		$mobile_browser++;
	}

	if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
		$mobile_browser++;
	}

	$mobile_ua = strtolower(substr($agent, 0, 4));
	$mobile_agents = array(
		'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
		'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
		'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
		'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
		'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
		'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
		'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
		'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
		'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-');

	if (in_array($mobile_ua, $mobile_agents)) {
		$mobile_browser++;
	}

	if (strpos(strtolower($agent), 'opera mini') > 0) {
		$mobile_browser++;
		//Check for tablets on opera mini alternative headers
		$stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : ''));
		if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
			$tablet_browser++;
		}
	}

	if ($tablet_browser > 0) {
		return 'Tablet';
	} else if ($mobile_browser > 0) {
		return 'Mobil';
	} else {
		return 'Bilgisayar';
	}
}

function script_encode(string $str) : string
{
	$_ = [
		'/\<script(.*?)\>/i' => '&#60;script$1&#62;',
		'/\<\/script\>/i' => '&#60;/script&#62;'
	];

	return preg_replace
	(
		array_keys($_),
		array_values($_),
		$str
	);
}

function data_json($value)
{
	return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}

function slugify($input)
{
	return \Transliterator::createFromRules(
		':: ASCII;'
		. ':: NFD;'
		. ':: [:Nonspacing Mark:] Remove;'
		. ':: NFC;'
		. ':: [:Punctuation:] Remove;'
		. ':: Lower();'
		. '[:Separator:] > \'-\''
	)
		->transliterate($input);
}

function generate_password($length = 20)
{
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' .
		'0123456789`-=~!@#$%^&*()_+,./<>?;:[]{}\|';

	$str = '';
	$max = strlen($chars) - 1;

	for ($i = 0; $i < $length; $i++)
		$str .= $chars[mt_rand(0, $max)];

	return $str;
}
?>
