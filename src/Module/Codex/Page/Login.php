<?php namespace Eternity2\Module\Codex\Page;

use Eternity2\Module\Codex\Module;
use Eternity2\Module\SmartPageResponder\Responder\SmartPageResponder;
use Eternity2\System\Module\ModuleLoader;

/**
 * @css       /~admin/css/style.css
 * @js        /~admin/js/login.js
 * @title     Admin
 * @bodyclass login
 * @template "@codex/Login.twig"
 */
class Login extends SmartPageResponder{

	/** @var \Eternity2\Module\Codex\Module */
	protected $module;

	public function __construct(){
		parent::__construct();
		$this->module = ModuleLoader::Service()->get(Module::class);
		$this->title = $this->module->getAdmin()['title'];
	}

	function prepare(){
		$this->getDataBag()->set('admin', $this->module->getAdmin());
	}

}