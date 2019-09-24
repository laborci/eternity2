<?php namespace Eternity2\Module\Zuul\Interfaces;

interface AuthRepositoryInterface {

	public function authLookup($id):?AuthenticableInterface;
	public function authLoginLookup($login):?AuthenticableInterface;

}