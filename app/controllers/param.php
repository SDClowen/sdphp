<?php 
class Param extends Controller
{
    public function getContentFromApi($url, $query)
    {
        $payload = json_encode(["query" => $query]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,3000);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        
        $result = curl_exec($ch);
        
        if(curl_errno($ch))	
            return false;
        
        curl_close($ch);
        
        return $result;
    }
    
    function index()
    {
        $dbName = "test";

        $db = new PDO("mysql:host=localhost;dbname=$dbName", "root", "");
        $sql = "SHOW TABLES";
         
        $statement = $db->prepare($sql);
         
        $statement->execute();
         
        $tables = $statement->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);

        foreach($tables as $table => $v)
        {
            $db->query("TRUNCATE TABLE $table"); 
            
            $result = json_decode($this->getContentFromApi("https://example.api", "SELECT * FROM $table"), true);
            
            if(is_array($result) &&count($result) > 0)
            {
                $first = $result[0];
                $sql = "INSERT INTO $table (". implode(',', array_keys($first)) .") VALUES (". substr(str_repeat('?,', sizeof($first)), 0, -1) .");";
                $prepStatement = $db->prepare($sql);
                
                foreach ( $result as $row ) 
                    $prepStatement->execute(array_values($row));
            }
            
            echo $table."\tsync has been finished ".count((array)$result)."\n";
        }
    }
    
    function parameter_type_test_float(float $num)
    {
        echo $num;
    }
    
    function parameter_type_test(int $num)
    {
        echo $num;
    }
    
    function parse(array $args, array $text)
    {
        echo "";
    }
    
	function optional($name, $surname, ...$params)
	{
        echo "<pre>";
		$param = $params[0][0];
		array_shift($params[0]);
		
		echo "name: $name <br>";
		echo "surname: $surname <br>";
		echo "param: {$param} <br>";
		
		echo "<font color=gray>".print_r($params, true)."</font></br>";
	}
	
	function noptional($name, $surname, array $params)
	{
        echo "<pre>";
		$param = $params[0];
		array_shift($params);
		
		echo "name: $name <br>";
		echo "surname: $surname <br>";
		echo "param: $param <br>";
		
		echo "<font color=gray>".print_r($params, true)."</font></br>";
	}
    
	function optional4( array $params)
	{
		echo "<font color=gray>".print_r($params, true)."</font></br>";
	}
    
    function optional2(...$params)
    {
        echo "<pre><font color=gray>".print_r(func_get_args(), true)."<br>".print_r($_GET, true)."</font></br>";
    }
    
    function optional3()
    {
        echo "<pre><font color=gray>".print_r(func_get_args(), true)."<br>".print_r($_GET, true)."</font></br>";
    }
}
?>
