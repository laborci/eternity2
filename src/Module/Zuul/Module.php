<?php namespace Eternity2\Module\Zuul;

use Eternity2\Module\Zuul\Interfaces\AuthenticableInterface;
use Eternity2\Module\Zuul\Interfaces\AuthRepositoryInterface;
use Eternity2\Module\Zuul\Interfaces\AuthServiceInterface;
use Eternity2\Module\Zuul\Interfaces\AuthSessionInterface;
use Eternity2\Module\Zuul\Interfaces\WhoAmIInterface;
use Eternity2\System\Module\ModuleInterface;
use Eternity2\System\ServiceManager\ServiceContainer;

class Module implements ModuleInterface{

	public function __invoke($env){
		ServiceContainer::shared(AuthServiceInterface::class)->service(AuthService::class);
		ServiceContainer::shared(AuthSessionInterface::class)->service(AuthSession::class);
		ServiceContainer::shared(WhoAmIInterface::class)->service(WhoAmI::class);

		ServiceContainer::shared(AuthenticableInterface::class)->service($env['services']['Authenticable']);
		ServiceContainer::shared(AuthRepositoryInterface::class)->service($env['services']['AuthRepository']);
	}


}


