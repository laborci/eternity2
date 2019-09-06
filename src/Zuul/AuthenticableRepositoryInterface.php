<?php namespace Eternity2\Zuul;

interface AuthenticableRepositoryInterface {

	public function authLookup($id):AuthenticableInterface;
	public function authLoginLookup($login):?AuthenticableInterface;

}