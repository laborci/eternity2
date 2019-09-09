<?php namespace Eternity2\Module\Codex\Codex;

use Eternity2\System\ServiceManager\Service;
use Eternity2\System\ServiceManager\SharedService;
class AdminRegistry implements SharedService{

	use Service;

	protected $admins = [];

	public function registerForm($form, $config=null){
		$this->admins[(new \ReflectionClass($form))->getShortName()] = ['form'=>$form, 'config'=>$config];
	}

	public function get($name):AdminDescriptor{
		$form = $this->admins[$name]['form'];
		/** @var \Eternity2\Module\Codex\Codex\AdminDescriptor $codex */
		$codex = $form::Service();
		$codex->setConfig($this->admins[$name]['config']);
		return $codex;
	}
}