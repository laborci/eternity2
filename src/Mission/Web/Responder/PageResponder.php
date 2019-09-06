<?php namespace Eternity2\Mission\Web\Responder;

use Eternity2\Mission\Web\Pipeline\Responder;

abstract class PageResponder extends Responder {

	public function __invoke($method = null) {
		$this->prepare();
		if(method_exists($this, 'shutDown')) register_shutdown_function([$this, 'shutDown']);
		$this->getResponse()->setContent($this->respond());
		$this->next();
	}
	protected function prepare(){}
}