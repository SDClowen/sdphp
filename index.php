<?php
	define('ROOT_DIR', __DIR__);
	define('APP_DIR', ROOT_DIR.'/app');
	define('LANG_DIR', APP_DIR.'/lang');
	define('CORE_DIR', ROOT_DIR.'/core');
	define('MDIR', APP_DIR.'/models');
	define('VDIR', APP_DIR.'/views');
	define('CDIR', APP_DIR.'/controllers');
	define('HDIR', APP_DIR.'/helpers');
	define('COREHDIR', CORE_DIR.'/helpers');
	define('DIRECT', false);
	define('DEBUG', true);

	# CSRF hatasına sebep oluyor remove
	/* session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true
    ]); */
    
	if(DEBUG)
	{
		ini_set("display_errors", 1);
		error_reporting(E_ALL);
	}
	else
		error_reporting(0);

	# include core helpers
	foreach(scandir(COREHDIR) as $file)
		if($file[0] != ".")
			require_once COREHDIR."/".$file;
	
	# include app helpers
	foreach(scandir(HDIR) as $file)
		if($file[0] != ".")
			require_once HDIR."/".$file;
	
	spl_autoload_register(function ($class)
	{
		$class = strtolower($class);
		$pathCore = CORE_DIR."/$class.php";
		$modelCore = MDIR."/$class.php";
		$attrCore = CORE_DIR."/attributes/$class.php";
		$pathHelpers = APP_DIR."/helpers/$class.php";

		if(file_exists($pathCore) && is_readable($pathCore))
			require_once $pathCore;
		else if(file_exists($modelCore) && is_readable($modelCore))
			require_once $modelCore;
		else if(file_exists($attrCore) && is_readable($attrCore))
			require_once $attrCore;
		else if(file_exists($pathHelpers) && is_readable($pathHelpers))
			require_once $pathHelpers;
		else
			debug("spl_autoload_register::$class not found!");
	});
	
	#require_once CORE_DIR."/cache.php";
	require_once CORE_DIR.'/app.php';
	require_once CORE_DIR.'/model.php';
	require_once CORE_DIR.'/controller.php';

	session_start();

	$browserLang = str_replace("-", "_", Request::getLocale());

	$lang = "tr_TR";
	if(session_check("langChanged"))
		$lang = session_get("lang");
	elseif(file_exists(LANG_DIR."/".$browserLang.".php"))
		$lang = $browserLang;
	#else
		#$lang = Config::get()->lang;
		
	if(!file_exists(LANG_DIR."/".$lang.".php"))
		$lang = "en_US";

	session_set("lang", $lang);

	setlocale(LC_ALL, 'tr_TR.utf8');
	date_default_timezone_set("Europe/Istanbul");

	#include the language file
	$GLOBALS["Language"] = include_once LANG_DIR."/$lang.php";
	
	$app = new App;
	$app->run();
?>
