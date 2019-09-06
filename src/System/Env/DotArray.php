<?php namespace Eternity2\System\Env;

class DotArray{
	public static function set(&$array, $key, $value){
		if (is_null($key)) return $array = $value;
		$keys = explode('.', $key);
		while (count($keys) > 1){
			$key = array_shift($keys);
			if (!isset($array[$key]) || !is_array($array[$key])){
				$array[$key] = [];
			}
			$array = &$array[$key];
		}
		$array[array_shift($keys)] = $value;
		return $array;
	}
	public static function get($array, $key, $default = []){
		if (!static::accessible($array)) return static::value($default);
		if (is_null($key)) return $array;
		if (static::exists($array, $key)) return $array[$key];
		if (strpos($key, '.') === false) return $array[$key] ?? static::value($default);
		foreach (explode('.', $key) as $segment){
			if (static::accessible($array) && static::exists($array, $segment)) $array = $array[$segment];
			else return static::value($default);
		}
		return $array;
	}
	protected static function accessible($value){ return is_array($value) || $value instanceof \ArrayAccess; }
	protected static function exists($array, $key){ return ($array instanceof \ArrayAccess) ? $array->offsetExists($key) : array_key_exists($key, $array); }
	protected static function value($value){ return $value instanceof \Closure ? $value() : $value; }
}