<?php

namespace Core;
use Core\Attributes\route;

final class App
{
	private static $extraPath = "";
    private static $controller = "Main"; 
    private static $action = "index";
    private static $params = [];
    private static $config;

    public static function weakup($appDir)
    {
        define('ROOT_DIR', $appDir);
        define('APP_DIR', ROOT_DIR . '/app');
        define('LANG_DIR', APP_DIR . '/lang');

        global $appConfig;
        $appConfig = (object)require_once(APP_DIR."/config/app.php");
        
        define('CORE_DIR', __DIR__);
        define('MDIR', APP_DIR . '/models');
        define('VDIR', APP_DIR . '/views');
        define('CDIR', APP_DIR . '/controllers');
        define('HDIR', APP_DIR . '/helpers');
        define('COREHDIR', CORE_DIR . '/helpers');
        define('DEBUG', $appConfig->mode == "development");

        # include core helpers
        foreach (scandir(COREHDIR) as $file)
            if ($file[0] != ".")
                require_once COREHDIR . "/" . $file;

        # include app helpers
        foreach (scandir(HDIR) as $file)
            if ($file[0] != ".")
                require_once HDIR . "/" . $file;

        session_start();

        # CSRF hatasına sebep oluyor remove
        /* session_start([
            'cookie_httponly' => true,
            'cookie_secure' => true
        ]); */

        if (DEBUG) {
            ini_set("display_errors", "On");
            error_reporting(E_ALL);
        } else
            error_reporting(0);

        global $cookie;
        $cookie = Cookie::instance();

        if (!$cookie->has("lang")) {

            $lang = str_replace("-", "_", Request::getLocale());
            $lang = "tr_TR"; # cuz of we cant find the default browser language!!!!

            if (!file_exists(LANG_DIR . "/" . $lang . ".php"))
                $lang = "tr_TR";

            $cookie->set("lang", $lang, 24);
        } else
            $lang = $cookie->get("lang");

        setlocale(LC_ALL, $appConfig->locale);
        date_default_timezone_set($appConfig->timezone);

        #include the language file
        $GLOBALS["APP_LANGUAGE"] = include_once LANG_DIR . "/$lang.php";
    
        return self::run();
    }

    /*
        TODO: Url'yi normalize et
        https://en.wikipedia.org/wiki/URI_normalization
    */
    private static function initialize()
    {
        self::$config = (object)require_once(APP_DIR."/config/core.php");

        $url = Request::segments();
        
        #if (count($url) && !check_url_segments_security($url)) 
            #debug("<br><font color=red>URL_FAILED</font><br>");
        
        if (!isset($url[0]))
            return;
            
		if(file_exists(CDIR."/".$url[0]))
		{
			self::$extraPath = $url[0];
			self::$controller = "Main";
			array_shift($url);
			
			if(count($url) == 0)
				return;
		}
	
		if (!file_exists(CDIR."/".self::$extraPath."/".ucfirst($url[0]).".php")) 
        {
            self::$action = $url[0];

            if(self::$action == "404" || self::$action == "403")
            {
                #header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
                die(file_get_contents(VDIR."/errors/".self::$action.".html"));
            }

            array_shift($url);
        }
        else
        {
            self::$controller = ucfirst($url[0]);
            array_shift($url);

            if (isset($url[0]))
            {
                self::$action = $url[0];
                array_shift($url);
            }
        }
        
        self::$params = $url;
    }

    private static function findMethod(&$controller, string $name, $extraPath = "")
    {
        $path = "App\\Controllers\\".(empty($extraPath) ? $controller : self::$extraPath."\\".self::$controller);
        
        $reflection = new \ReflectionClass($path);
        $controller = $reflection->newInstance();
        
        $method = route::get;
        $xhr = Request::isAjax();
        switch(mb_strtoupper(Request::getRequestMethod()))
        {
            case "GET":
                $method = $xhr ? route::xhr_get : route::get; 
                break;

            case "POST":
                $method = $xhr ? route::xhr_post : route::post; 
                break;

            case "PUT":
                #$method = $xhr ? route::xhr_get : route::normal_get; 
                break;
        }

        foreach($reflection->getMethods() as $k => $value)
        {
            $instance = null;
            if($attributes = $value->getAttributes())
                $instance = current($attributes)->newInstance();

            if($instance == null)
                $instance = new route;

            $compare = function() use($instance, $method) {
                if($instance == null)
                    return false;

                return $instance->method & $method;
            };

            if((strcasecmp($value->name, $name) === 0 || 
                ($instance != null && strcasecmp($instance->uri, $name) === 0)) 
                && $compare()
            )
            {
                if(!empty($instance?->uri) && $instance?->uri != self::$action)
                    debug("Function not reliable with instance!");
                    
                return (object)["method" => $value, "instance" => $instance];
            }
        }
    
        return null;
    }
    
    public static function run()
    {
		$start_time = microtime(true);
        self::initialize();

        $controllerPath = CDIR."/".self::$extraPath."/".self::$controller.".php";

        if(!file_exists($controllerPath))
            debug("Controller Path not found\n $controllerPath");
            
        #require_once $controllerPath;

        $action = self::findMethod(self::$controller, self::$action, self::$extraPath);
        if(!$action)
            debug("The method not found: ".self::$action."\n");

        # TODO: Work on it!!!!
        if($action->instance?->method & route::xhr_all && ($xhrSleep = self::$config->xhr_wait_milliseconds) > 0)
            usleep($xhrSleep * 1000);

        if($action->instance?->session && !session_check($action->instance?->session))
        {
            $otherwise = "/";
            if($action->instance->otherwise)
                $otherwise = $action->instance->otherwise;

            if(Request::isAjax())
                error(redirect: $otherwise);
            else
                redirect($otherwise);
        }

        if($action->method->getNumberOfRequiredParameters() > count(self::$params))
            debug("Param Count Error");
        
        $parameters = $action->method->getParameters();
        $params = &self::$params;
        
        self::$controller->db = Database::get();
        self::$controller->config = Config::get();
        self::$controller->attributes = $action->instance;

        // gelen params ile fonksiyondaki params'ın tipleri uyuşuyormu kontrol et
        for($i = 0; $i < count($parameters); $i++)
        {
            $parameter = $parameters[$i];
            if($parameter->isOptional() && count($params) <= $i)
                continue;

            if($parameter->hasType())
            {
                $type = (string)($parameter->getType() ? $parameter->getType()->getName() : null);
                
                if($type == "array")
                {
                    //echo $params[$i];
                    if(($scheme = explode('?', $params[$i])) && count($scheme) != 2)
                        debug("Array count error");
                    
                    echo "<br>::Param is array:: <d style='color : gray'>$params[$i]</d><br>";
                    parse_str(parse_url("?".$scheme[1], PHP_URL_QUERY), $output);
                    
                    if($output && check_url_segments_security($output))
                    {
                        array_unshift($output, $scheme[0]);
                        
                        $params[$i] = $output;
                        print_r($params);
                    }
                }
                elseif(($type == "float" || $type == "int") && !is_numeric($params[$i]))
                {
                    debug("Parameter types do not match => Need Type: <b style='color: blue'>".$parameter->getType()."</b> - Incoming Type: <b style='color: gray'>".gettype($params[$i])."<br>");
                    return;
                }
            }
        }
        
        $action->method->invokeArgs(self::$controller, self::$params);
		
		$end_time = microtime(true);
		$elapsed = $end_time - $start_time;
        $elapsed += $start_time - $_SERVER["REQUEST_TIME_FLOAT"];
		return number_format($elapsed * 1000, 2);
    }
}
