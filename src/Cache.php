<?php 
namespace Core;

class Cache 
{
	private  $cache = null;
	private  $time = 60;
	private  $status = 0;
	private  $dir = "./Cache";
	private  $buffer=false;
	private  $start=null;
	private  $load=false;
	private  $external= [];
	private  $type=true;
	private  $extension=".html";
	private  $active=true;
	
	public function __construct($options=NULL,$active=true)
	{
		$this->active	=	$active;
		if ($active) {
			
			
			if (isset($options) && is_array($options)) {
				if(isset($options['dir']))    	  $this->dir = $options['dir'];
				if(isset($options['buffer'])) 	  $this->buffer = $options['buffer'];
				if(isset($options['time']))   	  $this->time = $options['time'];
				if(isset($options['load']))  	  $this->load = $options['load'];
				if(isset($options['external']))   $this->external = $options['external'];
				if(isset($options['extension']))   $this->extension = $options['extension'];
			}
			$this->type = in_array($_SERVER["REQUEST_URI"],$this->external);
			
			if ($this->type) {
			
				if(!file_exists(dirname(__FILE__)."/".$this->dir)){
					mkdir(dirname(__FILE__)."/".$this->dir, 0777);
				}
				if ($this->load) {
						list($time[1], $time[0]) = explode(' ', microtime());
						$this->start = $time[1] + $time[0];
				}
			
				
				 $this->cache  =  dirname(__FILE__)."/".$this->dir."/".md5($_SERVER['REQUEST_URI']).$this->extension;
				 if(time() - $this->time < @filemtime($this->cache)) { 
				      readfile($this->cache); 
				      $this->status=1;
				      die();
				}else { 
				 
			      @unlink($this->cache); 
				  ob_start();
				}
			}
		}
	}
	
	private function buffer($buffer){
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		$buffer = str_replace(': ', ':', $buffer);
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '    ', '    '), '', $buffer);
		return $buffer;
	}
	
	private function writeCache($content)
	{
		$file = fopen($this->cache, 'w');
		$content=$content;
		@fwrite($file, $content);
		fclose($file);
	}
	
	public function clearCache()
	{
		$dir = opendir($this->dir); 
		while (($file = readdir($dir)) !== false) 
		{
		if(! is_dir($file)){
		  unlink($this->dir."/".$file);
		}}
		closedir($dir); 
	}
	
	public function __destruct(){
		if ($this->active) {
			if ($this->type) {
				if ($this->status==0) {
					if ($this->buffer) {
						$this->writeCache($this->buffer(ob_get_contents()));
					}else{
						$this->writeCache(ob_get_contents());
					}
				}
				if ($this->load) {
						list($time[1], $time[0]) = explode(' ', microtime());
						$finish = $time[1] + $time[0];
						$total_time = number_format(($finish - $this->start), 6);
						
						if(!Request::isAjax())
							echo "{$total_time} saniyede yüklendi.";
				}
				ob_end_flush();
			}
		}
	}
}
?>