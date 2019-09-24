<?php namespace Eternity2\Module\Zuul\Auth\Middleware;

use Eternity2\Mission\Web\Pipeline\Middleware;
use Eternity2\Module\Zuul\Interfaces\AuthServiceInterface;

class AuthCheck extends Middleware {

	protected $authService;

	public function __construct(AuthServiceInterface $authService) {
		$this->authService = $authService;
	}

	protected function run() {
		$responder = $this->getArgumentsBag()->get('responder');
		if (!$this->authService->isAuthenticated()){
			$this->authService->logout();
			$this->break($responder);
		} else {
			$this->next();
		}
	}

	static public function config($responder){
		return ['responder'=>$responder];
	}


}
