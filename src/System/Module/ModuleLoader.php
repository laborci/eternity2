<?php namespace Eternity2\System\Module;

use Eternity2\System\ServiceManager\Service;
use Eternity2\System\ServiceManager\ServiceContainer;
use Eternity2\System\ServiceManager\SharedService;

class ModuleLoader implements SharedService{
	use Service;

	protected $modules = [];

	public function loadModules(array $modules){ foreach ($modules as $module => $config) $this->loadModule($module, $config); }

	public function loadModule(string $module, $env){
		/** @var \Eternity2\System\Module\ModuleInterface $moduleInstance */
		$moduleInstance = ServiceContainer::get($module);
		$key = get_class($moduleInstance);
		if (array_key_exists($key, $this->modules)) throw new \Exception('Module already loaded: ' . $key);
		$this->modules[$key] = $moduleInstance;
		$moduleInstance($env);
	}

	public function get($module): ModuleInterface{ return $this->modules[$module]; }
}