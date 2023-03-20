<?php
	
	if (!function_exists('each')) {
		function each(array &$array) {
			$value = current($array);
			$key = key($array);

			if (is_null($key)) {
				return false;
			}

			// Move pointer.
			next($array);

			return array(1 => $value, 'value' => $value, 0 => $key, 'key' => $key);
		}
	}

	function array_sort($array, $on, $order=SORT_ASC)
	{
	    $new_array = array();
	    $sortable_array = array();

	    if (count($array) > 0) {
	        foreach ($array as $k => $v) {
	            if (is_array($v)) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $on) {
	                        $sortable_array[$k] = $v2;
	                    }
	                }
	            } else {
	                $sortable_array[$k] = $v;
	            }
	        }

	        switch ($order) {
	            case SORT_ASC:
	                asort($sortable_array);
	            break;
	            case SORT_DESC:
	                arsort($sortable_array);
	            break;
	        }

	        foreach ($sortable_array as $k => $v) {
	            $new_array[$k] = $array[$k];
	        }
	    }

	    return $new_array;
	}

	function array_values_by_key($array, $key) 
	{
	  $result = [];

	  foreach($array as $k => $v) 
	  	if(is_object($v))
			$result[] = $v->{$key};
		else
			$result[] = $v[$key];

	  return $result;
	}