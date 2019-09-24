<?php namespace Eternity2\Module\Zuul\Interfaces;

interface AuthServiceInterface {

	public function isAuthenticated():bool;
	public function getAuthenticatedId():int;
	public function login($login, $password, $permission = null): bool;
	public function checkPermission($permission): bool;
	public function logout();

}