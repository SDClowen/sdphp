<?php
defined("DIRECT") or exit("No direct script access allowed");

class App
{
	private $extraPath = "";
    private $controller = "Main"; 
    private $action = "index";
    private $params = [];
    private $config;

    /*
        TODO: Url'yi normalize et
        https://en.wikipedia.org/wiki/URI_normalization
    */
    public function __construct()
    {
        $this->config = (object)require_once(APP_DIR."/config/core.php");

        $url = Request::segments();
        
        if (count($url) && !check_url_segments_security($url)) 
            debug("<br><font color=red>URL_FAILED</font><br>");
        
        if (!isset($url[0]))
            return;
            
		if(file_exists(CDIR."/".$url[0]))
		{
			$this->extraPath = $url[0];
			$this->controller = "main";
			array_shift($url);
			
			if(count($url) == 0)
				return;
		}
	
		if (!file_exists(CDIR."/".$this->extraPath."/".$url[0].".php")) 
        {
            $this->action = $url[0];

            if($this->action == "404" || $this->action == "403")
            {
                #header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
                die(file_get_contents(VDIR."/errors/{$this->action}.html"));
            }

            array_shift($url);
        }
        else
        {
            $this->controller = $url[0];
            array_shift($url);

            if (isset($url[0]))
            {
                $this->action = $url[0];
                array_shift($url);
            }
        }
        
        $this->params = $url;
    }

    private function findMethod(&$controller, string $name, $xhr = false, $methodType = "GET")
    {
        $reflection = new ReflectionClass($controller);
        $controller = $reflection->newInstance();
        
        foreach($reflection->getMethods() as $k => $value)
        {
            $instance = null;
    
            if($attributes = $value->getAttributes())
                $instance = current($attributes)->newInstance();

            if((strcasecmp($value->name, $name) === 0 || 
                ($instance != null && strcasecmp($instance->uri, $name) === 0)) &&
               strcasecmp($instance == null ? "GET" : $instance->method, $methodType) === 0 &&
               $instance?->xhr == $xhr)
            {
                if(!empty($instance?->uri) && $instance?->uri != $this->action)
                    debug("Function not reliable with instance!");
                    
                return (object)["method" => $value, "instance" => $instance];
            }
        }
    
        return null;
    }
    
    public function run()
    {
		$start_time = microtime(TRUE);
		
        $controllerPath = CDIR."/".$this->extraPath."/".strtolower($this->controller).".php";
        if(!file_exists($controllerPath))
            debug("Controller Path not found\n $controllerPath");
            
        require_once $controllerPath;
        $action = $this->findMethod($this->controller, $this->action, Request::isAjax(), Request::getRequestMethod());
        if(!$action)
            debug("The method not found: $this->action\n");

        if($action->instance?->xhr && ($xhrSleep = $this->config->xhr_wait_milliseconds) > 0)
            usleep($xhrSleep * 1000);

        if($action->instance?->session && !session_check($action->instance?->session))
        {
            if(Request::isAjax())
                error(redirect: $action->instance->otherwise);
            else
                redirect($action->instance->otherwise);
        }

        if($action->method->getNumberOfRequiredParameters() > count($this->params))
            debug("Param Count Error");
        
        $parameters = $action->method->getParameters();
        $params = &$this->params;
        
        $this->controller->db = Database::instance();
        $this->controller->attributes = $action->instance;

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
        
        $action->method->invokeArgs($this->controller, $this->params);
		
		$end_time = microtime(TRUE);
		$elapsed = $end_time - $start_time;
		console("Page loaded in: $elapsed ms");
		return $elapsed;
    }
}
?>
