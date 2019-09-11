<?php namespace Eternity2\Module\Auth;

use Application\Service\Auth\UserLogger;
use Eternity2\Mission\Web\Responder\JsonResponder;
use Eternity2\Zuul\AuthServiceInterface;

class AuthAction extends JsonResponder {

	protected $authService;
	protected $userLogger;

	public function __construct(AuthServiceInterface $authService, UserLogger $userLogger) {
		$this->authService = $authService;
		$this->userLogger = $userLogger;
	}

	protected function respond() {
		$method = $this->getArgumentsBag()->get('method');
		switch ($method) {
			case 'login':
				if (!$this->authService->login($this->getRequestBag()->get('login'), $this->getRequestBag()->get('password'), 'admin')) {
					$this->getResponse()->setStatusCode('401');
				} else {
					$this->userLogger->log($this->authService->getAuthenticatedId(), UserLogger::ADMINLOGIN);
				}
				break;
			case 'logout':
				$this->userLogger->log($this->authService->getAuthenticatedId(), UserLogger::ADMINLOGOUT);
				$this->authService->logout();
				break;
		}

	}
}