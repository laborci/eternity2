<?php namespace Eternity2\System\Module;

interface ModuleInterface extends \Eternity2\System\ServiceManager\SharedService{
	public function __invoke($env);
}