<?php namespace Eternity2\Module\Codex\Page;


use Eternity2\Module\Codex\CodexWhoAmIInterface;
use Eternity2\Module\Codex\Module;
use Eternity2\Module\SmartPageResponder\Responder\SmartPageResponder;
use Eternity2\System\Module\ModuleLoader;
use Eternity2\Module\Zuul\Interfaces\WhoAmIInterface;

/**
 * @css /~admin/css/style.css
 * @js  /~admin/js/app.js
 * @title Admin
 * @template "@codex/Index.twig"
 */
class Index extends SmartPageResponder {

	private $whoAmI;

	/** @var \Eternity2\Module\Codex\Module  */
	protected $module;

	public function __construct(CodexWhoAmIInterface $whoAmI) {
		parent::__construct();
		$this->whoAmI = $whoAmI;
		$this->module = ModuleLoader::Service()->get(Module::class);
		$this->title = $this->module->getAdmin()['title'];
	}

	function prepare() {
		$this->getDataBag()->set('admin', $this->module->getAdmin());
		$this->getDataBag()->set('user', $this->whoAmI->getName());
		$this->getDataBag()->set('avatar', $this->whoAmI->getAvatar());
	}

}