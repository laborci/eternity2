<?php namespace Eternity2\Module\Zuul\Interfaces;

interface AuthenticableInterface {
	public function getId():int;
	public function checkPassword($password):bool;
	public function checkPermission($permission):bool;
}