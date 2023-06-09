<?php
namespace Core;

class Validate
{
    public static function check(string $function, $requestMethod = "post", $param = null, $filter = true)
    {
        $parse = explode("::", $function);
        if(count($parse) != 2)
            die(__METHOD__."($function) parsing count error!");

        $className = $parse[0];
        $methodName = $parse[1];

        $path = APP_DIR."/validate/".$className.".php";
        if(!file_exists($path))
            die(__METHOD__." $path not found!");

        $array = require_once $path;
        if(!isset($methodName, $array))
            die(__METHOD__." the $methodName key not found in $function");

        $array[$methodName];

        $data = null;

        switch (strtolower($requestMethod)) {
            case 'post':
                $data = Request::post($param, $filter);
                break;
            
            case 'get':
                $data = Request::get($param, $filter);
                break;
        }

        $errors = validate($source, $items);
        if($errors)
            return $errors;
        
        return false;
    }
}