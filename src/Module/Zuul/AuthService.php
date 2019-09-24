<?php namespace Eternity2\Module\Zuul;

use Eternity2\System\Event\EventManager;
use Eternity2\Module\Zuul\Interfaces\AuthenticableInterface;
use Eternity2\Module\Zuul\Interfaces\AuthRepositoryInterface;
use Eternity2\Module\Zuul\Interfaces\AuthServiceInterface;
use Eternity2\Module\Zuul\Interfaces\AuthSessionInterface;

class AuthService implements AuthServiceInterface{

	protected $session;
	protected $repository;

	public function __construct(AuthSessionInterface $session, AuthRepositoryInterface $repository){
		$this->session = $session;
		$this->repository = $repository;
	}

	public function login($login, $password, $permission = null): bool{
		$user = $this->repository->authLoginLookup($login);

		if(!$user){
			EventManager::fire(Event::EVENT_LOGIN_ERROR_USER_NOT_FOUND, $login);
			return false;
		}

		if(!$user->checkPassword($password)){
			EventManager::fire(Event::EVENT_LOGIN_ERROR_WRONG_PASSWORD, $login);
			return false;
		}

		if(!(is_null($permission) || $user->checkPermission($permission))){
			EventManager::fire(Event::EVENT_LOGIN_ERROR_WRONG_PERMISSION, $login);
			return false;
		}

		$this->registerAuthSession($user);
		return true;
	}

	public function logout(){ $this->clearAuthSession(); }

	public function isAuthenticated(): bool{ return (bool)$this->session->getUserId(); }
	public function getAuthenticatedId(): int{ return $this->session->getUserId(); }

	public function checkPermission($permission): bool{
		if(!$this->isAuthenticated()) return false;
		if(!$this->repository->authLookup($this->session->getUserId())) return false;
		return $this->repository->authLookup($this->session->getUserId())->checkPermission($permission);
	}


	protected function registerAuthSession(AuthenticableInterface $user){ $this->session->setUserId($user->getId()); }
	protected function clearAuthSession(){ $this->session->forget(); }


}