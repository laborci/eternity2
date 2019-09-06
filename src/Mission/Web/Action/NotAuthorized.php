<?php namespace Eternity2\Mission\Web\Action;

use Eternity2\Mission\Web\Responder\PageResponder;

class NotAuthorized extends PageResponder {

	protected function respond() {
		$this->getResponse()->setStatusCode(401);
	}

}