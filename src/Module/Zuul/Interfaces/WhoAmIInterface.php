<?php namespace Eternity2\Module\Zuul\Interfaces;

interface WhoAmIInterface{
	public function checkPermission($permission):bool;
	public function isAuthenticated():bool;
	public function logout();
	public function __invoke():?int;
}