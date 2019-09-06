<?php namespace Eternity2\Module\SmartPageResponder;

use Eternity2\Module\SmartPageResponder\Twigger\Twigger;
use Eternity2\System\Event\EventManager;
use Eternity2\System\Module\ModuleInterface;

class Module implements ModuleInterface{
	public function __invoke($env){
		EventManager::listen(Twigger::EVENT_TWIG_ENVIRONMENT_CREATED, function () use ($env){
			Twigger::Service()->addPath(__DIR__ . '/@template', 'smartpage');
			if(array_key_exists('twig-sources', $env)) foreach ($env['twig-sources'] as $namespace=>$source){
				Twigger::Service()->addPath($source, $namespace);
			}
		});
	}

}