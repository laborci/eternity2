<?php namespace Eternity2\Module\Codex\Page;


use Application\Service\Auth\WhoAmI;
use Eternity2\Module\SmartPageResponder\Responder\SmartPageResponder;

/**
 * @css /admin/css/style.css
 * @js  /admin/js/app.js
 * @title Admin
 * @template "@codex/Index.twig"
 */
class Index extends SmartPageResponder {

	/** @var \Application\Service\Auth\WhoAmI */
	private $whoAmI;

	public function __construct(WhoAmI $whoAmI) {
		parent::__construct();
		$this->whoAmI = $whoAmI;
	}

	function prepare() {
		$this->getDataBag()->set('user', $this->whoAmI->getUser()->name);
		$this->getDataBag()->set('avatar', $this->whoAmI->getUser()->getCodexAvatar());
	}

}