<?php namespace Eternity2\Mission\Web;

use Eternity2\System\Event\EventManager;
use Eternity2\System\Mission\Mission;
use Eternity2\System\ServiceManager\ServiceContainer;
use Eternity2\System\ServiceManager\SharedService;
use Eternity2\Mission\Web\Routing\Router;

abstract class Application implements SharedService, Mission {

	const EVENT_ROUTING_BEFORE = 'EVENT_ROUTING_BEFORE';
	const EVENT_ROUTING_FINISHED = 'EVENT_ROUTING_FINISHED';
	const EVENT_ROUTING_NOTFOUND = 'EVENT_ROUTING_NOTFOUND';

	/** @var Router */
	protected $router;
	protected $env;

	public function run($env){
		$this->env = $env;
		$this->router = ServiceContainer::get(Router::class);
		$this->initialize();
		EventManager::fire(self::EVENT_ROUTING_BEFORE, $this->router);
		$this->route($this->router);
		EventManager::fire(self::EVENT_ROUTING_FINISHED, $this->router);
		EventManager::fire(self::EVENT_ROUTING_NOTFOUND, $this->router);
		die();
	}

	protected function initialize(){	}

	protected function route(Router $router){}

}