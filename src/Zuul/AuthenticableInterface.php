<?php namespace Eternity2\Zuul;

interface AuthenticableInterface {
	public function getId():int;
	public function checkPassword($password):bool;
	public function checkPermission($permission):bool;
}