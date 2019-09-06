<?php namespace Eternity2\Zuul;

interface AuthSessionInterface {

	public function setUserId($userId);
	public function getUserId();
	public function forget();


}