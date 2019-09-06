<?php namespace Eternity2\Module\Auth;

use Eternity2\System\Event\EventManager;
use Eternity2\System\Module\ModuleInterface;
use Eternity2\Mission\Web\Application;
use Eternity2\Mission\Web\Middleware\AuthCheck;
use Eternity2\Mission\Web\Middleware\PermissionCheck;
use Eternity2\Mission\Web\Routing\Router;

class Module implements ModuleInterface{

	protected $loginPage = false;
	protected $permission = false;

	public function __invoke($env){
		if (array_key_exists('login-page', $env)) $this->loginPage = $env['login-page'];
		if (array_key_exists('permission', $env)) $this->permission = $env['permission'];
		EventManager::listen(Application::EVENT_ROUTING_BEFORE, [$this, 'route']);
	}

	public function route(Router $router){
		$router->post("/login", AuthAction::class, ["method" => "login"])();
		if ($this->loginPage){
			$router->pipe(AuthCheck::class, ["responder" => $this->loginPage]);
			if ($this->permission){
				$router->pipe(PermissionCheck::class, ["responder" => $this->loginPage, "permission" => $this->permission, "logout-on-fail" => true]);
			}
		}
		$router->post("/logout", AuthAction::class, ["method" => "logout"])();
	}

}


