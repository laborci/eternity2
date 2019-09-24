<?php namespace Eternity2\Module\CodexAuth;

use Eternity2\Mission\Web\Application;
use Eternity2\Mission\Web\Routing\Router;
use Eternity2\System\Event\EventManager;
use Eternity2\System\Module\ModuleInterface;
use Eternity2\Module\Zuul\Auth\Action\Login;
use Eternity2\Module\Zuul\Auth\Action\Logout;
use Eternity2\Module\Zuul\Auth\Middleware\AuthCheck;
use Eternity2\Module\Zuul\Auth\Middleware\PermissionCheck;

class Module implements ModuleInterface{

	protected $loginPage = false;
	protected $permission = false;

	public function __invoke($env){
		if (array_key_exists('login-page', $env)) $this->loginPage = $env['login-page'];
		if (array_key_exists('permission', $env)) $this->permission = $env['permission'];
		EventManager::listen(Application::EVENT_ROUTING_BEFORE, [$this, 'route']);
	}

	public function route(Router $router){
		$router->post("/login", Login::class)();
		if ($this->loginPage){
			$router->pipe(AuthCheck::class, AuthCheck::config($this->loginPage));
			if ($this->permission){
				$router->pipe(PermissionCheck::class, PermissionCheck::config($this->loginPage, $this->permission, true));
			}
		}
		$router->post("/logout", Logout::class)();
	}

}


