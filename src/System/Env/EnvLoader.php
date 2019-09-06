<?php namespace Eternity2\System\Env;

use Symfony\Component\Yaml\Yaml;

class EnvLoader{

	protected $env = [];

	public static function load(){ return static::parseExpressions(static::loadYml(getenv('ini-file'))); }
	public static function save(){
		$content = "<?php return ".var_export(static::load(), true).';';
		file_put_contents(getenv('env-path') . getenv('env-build-file'), $content);
	}

	protected static function parseExpressions(array $array){
		foreach ($array as $key => $value){
			if (!is_array($value)){
				if (substr($key, 0, 1) === '~'){
					$array[substr($key, 1)] = getenv('root') . $value;
					unset($array[$key]);
					$key = substr($key, 1);
				}
				if(strpos($key, '.') !== false){
					DotArray::set($array, $key, $value);
					unset($array[$key]);
				}
			}else $array[$key] = static::parseExpressions($value);
		}
		return $array;
	}

	protected static function loadYml($file){
		$ini_file = getenv('ini-path') . $file . '.yml';
		$ini_local = getenv('ini-path') . $file . '.local.yml';

		$values = [];
		$loaded = Yaml::parseFile($ini_file);
		if (is_array($loaded)) $values = array_replace_recursive($values, $loaded);
		if (file_exists($ini_local)){
			$loaded = Yaml::parseFile($ini_local);
			if (is_array($loaded)) $values = array_replace_recursive($values, $loaded);
		}

		$env = [];
		foreach ($values as $key => $value){
			if (substr($key, 0, 1) === ':'){
				$key = substr($key, 1);
				$includes = $value;
				$value = [];
				if (is_string($includes)) $includes = [$includes];
				foreach ($includes as $include){
					$value = array_replace_recursive($value, static::loadYml($include));
				}
			}
			if (is_array($value)) $value = array_merge_recursive(DotArray::get($env, $key), $value);
			DotArray::set($env, $key, $value);
		}

		return $env;
	}
}