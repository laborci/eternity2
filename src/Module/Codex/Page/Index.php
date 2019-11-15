<?php namespace Eternity2\Module\Codex\Page;

use Eternity2\Module\Codex\CodexWhoAmIInterface;
use Eternity2\Module\Codex\Module;
use Eternity2\Module\SmartPageResponder\Responder\SmartPageResponder;
use Eternity2\System\Module\ModuleLoader;

/**
 * @title Admin
 * @template "@codex/Index.twig"
 */
class Index extends SmartPageResponder {

	private $whoAmI;

	/** @var \Eternity2\Module\Codex\Module  */
	protected $module;

	public function __construct(CodexWhoAmIInterface $whoAmI, ModuleLoader $moduleLoader) {
		parent::__construct();
		$this->module = $moduleLoader->get(Module::class);
		$this->css = [$this->module->getEnv()['frontend-prefix'].'app.css'];
		$this->js = [$this->module->getEnv()['frontend-prefix'].'app.js'];
		$this->whoAmI = $whoAmI;
		$this->title = $this->module->getAdmin()['title'];
	}

	function prepare() {
		$this->getDataBag()->set('admin', $this->module->getAdmin());
		$this->getDataBag()->set('user', $this->whoAmI->getName());
		$this->getDataBag()->set('avatar', $this->whoAmI->getAvatar());
	}

}