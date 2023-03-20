<?php 

function translate($q, $from, $to){
    $res= @file_get_contents("https://translate.googleapis.com/translate_a/single?client=gtx&ie=UTF-8&oe=UTF-8&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&sl=".$from."&tl=".$to."&hl=hl&q=".urlencode($q), $_SERVER['DOCUMENT_ROOT']."/transes.html");
    $res=json_decode($res);
    return $res[0][0][0];
}

/*


$langs = [];
foreach(scandir(__DIR__."/input/") as $file)
	if($file[0] != ".")
		$langs[$file] = require_once __DIR__."/input/".$file;
			
//example-- 
#echo translate("اسمي منتصر الصاوي", "ar", "en");

$translated = [];
foreach($langs as $file)
{
	foreach($file as $key => $text)
		$translated[$key] = translate($text, "tr", "en");
}

echo "<pre><code>";
print_r($translated);

*/
?>