<?php namespace Eternity2\System\Module;

use Eternity2\System\ServiceManager\Service;
use Eternity2\System\ServiceManager\ServiceContainer;
use Eternity2\System\ServiceManager\SharedService;

class ModuleLoader implements SharedService{
	use Service;

	protected $modules = [];

	public function loadModules(array $modules){ foreach ($modules as $module => $config) $this->loadModule($module, $config); }

	public function loadModule(string $module, $config){

		$moduleDesc = env('modules.' . $module);
		if ($moduleDesc){
			if (is_array($moduleDesc)){
				$moduleClass = $moduleDesc['class'];
				$moduleConfig = $moduleDesc['config'];
			}else{
				$moduleClass = $moduleDesc;
				$moduleConfig = [];
			}
		}else{
			$moduleClass = $module;
			$moduleConfig = [];
		}

		/** @var \Eternity2\System\Module\ModuleInterface $moduleInstance */
		$moduleInstance = ServiceContainer::get($moduleClass);
		$key = get_class($moduleInstance);
		if (array_key_exists($key, $this->modules)) throw new \Exception('Module already loaded: ' . $key);
		$this->modules[$key] = $moduleInstance;
		$this->modules[$module] = $moduleInstance;
		if (is_array($config)) $config = array_replace_recursive($moduleConfig, $config);
		$moduleInstance($config);
	}

	public function get($module): ModuleInterface{ return $this->modules[$module]; }
}