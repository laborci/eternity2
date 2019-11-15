<?php namespace Eternity2\Module\Codex\Page;

use Eternity2\Module\Codex\Module;
use Eternity2\Module\SmartPageResponder\Responder\SmartPageResponder;
use Eternity2\System\Module\ModuleLoader;

/**
 * @title     Admin
 * @bodyclass login
 * @template "@codex/Login.twig"
 */
class Login extends SmartPageResponder{

	/** @var \Eternity2\Module\Codex\Module */
	protected $module;

	public function __construct(ModuleLoader $moduleLoader) {
		parent::__construct();
		$this->module = $moduleLoader->get(Module::class);
		$this->css = [$this->module->getEnv()['frontend-prefix'].'login.css'];
		$this->js = [$this->module->getEnv()['frontend-prefix'].'login.js'];
		$this->title = $this->module->getAdmin()['title'];
	}

	function prepare(){
		$this->getDataBag()->set('admin', $this->module->getAdmin());
	}

}