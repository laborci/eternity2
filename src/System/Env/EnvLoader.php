<?php namespace Eternity2\System\Env;

use Symfony\Component\Yaml\Yaml;

class EnvLoader{

	protected $env = [];

	public static function checkCache(){
		$cacheFile = getenv('env-path') . getenv('env-build-file');
		if(!file_exists($cacheFile)) return false;
		$latestBuild = filemtime($cacheFile);
		$dir = new \RecursiveDirectoryIterator(getenv('ini-path'));
		$iterator = new \RecursiveIteratorIterator($dir);
		foreach ($iterator as $fileinfo){
			if ($fileinfo->getMTime() > $latestBuild) return false;
		}
		return true;
	}

	public static function load(){ return (new static())->loadEnv(); }

	public static function save(){
		$content = "<?php return " . var_export(static::load(), true) . ';';
		file_put_contents(getenv('env-path') . getenv('env-build-file'), $content);
	}

	protected function loadEnv(){
		$env = $this->loadYml(getenv('ini-file'));
		$env['root'] = $env['path']['root'] = getenv('root');
		$env = DotArray::flatten($env);
		$env = $this->pathFinder($env);
		foreach ($env as $key => $value) DotArray::set($env, $key, $value);
		return $env;
	}

	protected function pathFinder($env){
		$resolvables = [];
		foreach ($env as $key => $value){
			if (strpos($key, '~') !== false){

				if (strpos($key, '~(') !== false){
					preg_match('/~\((.*?)\)/', $key, $matches);
					$newKey = str_replace($matches[0], '', $key);
					$parent = $matches[1];
				}else{
					$newKey = str_replace('~', '', $key);
					$parent = 'root';
				}
				$path = [
					'newKey' => $newKey,
					'parent' => $parent,
					'value'  => $value,
				];
				$resolvables[$key] = $path;
			}
		}
		do{
			$count = count($resolvables);
			foreach ($resolvables as $key => $resolvable){
				if (array_key_exists($resolvable['parent'], $env)){
					$parent = $env[$resolvable['parent']];
					if (substr($parent, -1) !== '/') $parent .= '/';
					$env[$resolvable['newKey']] = $parent . $resolvable['value'];
					unset($env[$key]);
					unset($resolvables[$key]);
				}
			}
			if ($count === count($resolvables)) throw new \Exception('Env path reference not found '.reset($resolvables)['parent']);
		}while (count($resolvables));

		return $env;
	}

	protected function loadYml($file){
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
					$value = array_replace_recursive($value, $this->loadYml($include));
				}
			}
			if (is_array($value)) $value = array_replace_recursive(DotArray::get($env, $key), $value);
			DotArray::set($env, $key, $value);
		}

		return $env;
	}
}