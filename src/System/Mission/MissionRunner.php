<?php namespace Eternity2\System\Mission;

use Eternity2\System\Module\ModuleLoader;
use Eternity2\System\ServiceManager\ServiceContainer;
use Eternity2\System\StartupSequence\BootSequnece;
use Symfony\Component\HttpFoundation\Request;

class MissionRunner implements BootSequnece {

	function run() {
		$missions = env('missions');

		$host = Request::createFromGlobals()->getHttpHost();

		foreach ($missions as $mission) {
			$patterns = is_array($mission['pattern']) ? $mission['pattern'] : [$mission['pattern']];
			foreach ($patterns as $pattern) {
				$pattern = str_replace('{domain}', env('domain'), $pattern);
				if (fnmatch($pattern, $host)) {
					if (array_key_exists('reroute', $mission)) {
						die(header('location:' . Request::createFromGlobals()->getScheme() . '://' . str_replace('{domain}', env('domain'), $mission['reroute'])));
					}
					if(array_key_exists('modules', $mission)) ModuleLoader::Service()->loadModules($mission['modules']);
					/** @var Mission $missionary */
					$missionary = ServiceContainer::get($mission['mission']);
					$env = array_key_exists('config', $mission) ? $mission['config'] : null;
					$missionary->run($env);
					die();
				}
			}
		}
		die('No mission found');
	}
}