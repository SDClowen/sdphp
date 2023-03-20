<?php 
	ini_set("memory_limit", "-1");
	set_time_limit(0);
	
 	$server  =  'localhost'; 
    $username   = 'root'; 
    $password   = '1234';  
    $database = 'property';

    /* PDO connection start */
    $conn = new PDO("mysql:host=$server; dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);         
    $conn->exec("SET CHARACTER SET utf8");     
    /* PDO connection end */

    // your config
    $filename = 'propertydb.sql';

    $maxRuntime = 8; // less then your max script execution limit


    $deadline = time()+$maxRuntime; 
    $progressFilename = $filename.'_filepointer'; // tmp file for progress
    $errorFilename = $filename.'_error'; // tmp file for erro



    ($fp = fopen($filename, 'r')) OR die('failed to open file:'.$filename);

    // check for previous error
    if( file_exists($errorFilename) ){
        die('<pre> previous error: '.file_get_contents($errorFilename));
    }

    // activate automatic reload in browser
    echo '<html><head> <meta http-equiv="refresh" content="'.($maxRuntime+2).'"><pre>';

    // go to previous file position
    $filePosition = 0;
    if( file_exists($progressFilename) ){
        $filePosition = file_get_contents($progressFilename);
        fseek($fp, $filePosition);
    }

    $queryCount = 0;
    $query = '';
    while( $deadline>time() AND ($line=fgets($fp, 1024000)) ){
        if(substr($line,0,2)=='--' OR trim($line)=='' ){
            continue;
        }

        $query .= $line;
        if( substr(trim($query),-1)==';' ){

            $igweze_prep= $conn->prepare($query);

            if(!($igweze_prep->execute())){ 
                $error = 'Error performing query \'<strong>' . $query . '\': ' . print_r($conn->errorInfo());
                file_put_contents($errorFilename, $error."\n");
                exit;
            }
            $query = '';
            file_put_contents($progressFilename, ftell($fp)); // save the current file position for 
            $queryCount++;
        }
    }

    if( feof($fp) ){
        echo 'dump successfully restored!';
    }else{
        echo ftell($fp).'/'.filesize($filename).' '.(round(ftell($fp)/filesize($filename), 2)*100).'%'."\n";
        echo $queryCount.' queries processed! please reload or wait for automatic browser refresh!';
    }
