<?php 
class Arr
{
	private $index = 0;
	private $_array = [];

	public function __construct(array $array) {
        $this->_array = $array;
    }

	public function push($v)
	{
		$this->_array[] = $v;
	}

	public function current()
	{
		if(count($this->_array) <= $this->index)
			return false;
		
		return $this->_array[$this->index];
	}

	public function next()
	{
		if(count($this->_array) <= $this->index)
			return false;
		
		return $this->_array[$this->index++];
	}
	
	public function reset()
	{
		$this->index = 0;
	}

	public function take()
	{
		if(count($this->_array) == 0)
			return false;

		return $this->_array[0];
	}

	public function filter($callback)
	{
		return new Arr(array_filter($this->_array, $callback));
	}

	public function sum($key = null)
	{
		return array_reduce($this->_array, function($v, $i) use($key){
			if($key == null)
				return $v + $i;
			
			return $v + $i[$key];
		});
	}

	public function make($key)
	{
		$array = [];

		foreach($this->_array as $k => $v)
			if(isset($v[$key]))
				$array[] = $v[$key];

		return $array;
	}

	public function find(array $predicate)
	{
		foreach($this->_array as $k => $v)
			foreach ($predicate as $pK => $pV)
				if($v[$pK] == $pV)
					return $v;

		return null;
	}

	public function print()
	{
		print_r($this->_array);
	}
}
 ?>