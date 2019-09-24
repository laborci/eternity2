<?php namespace Eternity2\Module\Zuul\Auth\Action;

use Eternity2\Mission\Web\Responder\JsonResponder;
use Eternity2\Module\Zuul\Interfaces\AuthServiceInterface;

class Login extends JsonResponder{

	protected $authService;

	public function __construct(AuthServiceInterface $authService){
		$this->authService = $authService;
	}

	protected function respond(){
		if (!$this->authService->login($this->getRequestBag()->get('login'), $this->getRequestBag()->get('password'), 'admin')){
			$this->getResponse()->setStatusCode('401');
		}
	}

}