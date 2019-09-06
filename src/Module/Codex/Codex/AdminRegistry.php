<?php namespace Eternity2\Module\Codex\Codex;

use Eternity2\System\ServiceManager\Service;
use Eternity2\System\ServiceManager\SharedService;
class AdminRegistry implements SharedService{

	use Service;

	protected $admins = [];

	public function registerForm($form){
		$this->admins[(new \ReflectionClass($form))->getShortName()] = $form;
	}

	public function get($name):AdminDescriptor{
		$form = $this->admins[$name];
		return $form::Service();
	}
}