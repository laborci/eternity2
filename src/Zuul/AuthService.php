<?php namespace Eternity2\Zuul;

class AuthService implements AuthServiceInterface{

	protected $session;
	protected $repository;

	public function __construct(AuthSessionInterface $session, AuthenticableRepositoryInterface $repository){
		$this->session = $session;
		$this->repository = $repository;
	}

	public function login($login, $password, $permission = null): bool{
		$user = $this->repository->authLoginLookup($login);
		if ($user && $user->checkPassword($password) && (is_null($permission) || $user->checkPermission($permission))){
			$this->registerAuthSession($user);
			return true;
		}
		return false;
	}

	public function logout(){ $this->clearAuthSession(); }

	public function isAuthenticated(): bool{ return (bool)$this->session->getUserId(); }
	public function getAuthenticatedId(): int{ return $this->session->getUserId(); }

	public function checkPermission($permission): bool{
		return $this->isAuthenticated() ?
			$this->repository->authLookup($this->session->getUserId())->checkPermission($permission) :
			false;
	}


	public function registerAuthSession(AuthenticableInterface $user){ $this->session->setUserId($user->getId()); }
	public function clearAuthSession(){ $this->session->forget(); }


}