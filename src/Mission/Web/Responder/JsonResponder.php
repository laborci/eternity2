<?php namespace Eternity2\Mission\Web\Responder;

use Eternity2\Mission\Web\Pipeline\Responder;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class JsonResponder extends Responder {

	final public function __invoke($method = 'respond') {
		if (method_exists($this, 'shutDown')) {
			register_shutdown_function([$this, 'shutDown']);
		}
		$response = $this->getResponse();
		$response->headers->set('Content-Type', 'application/json');
		$response->setContent(json_encode($this->$method(), JSON_UNESCAPED_UNICODE));
		$this->next();
	}

	protected function respond() { return null; }

	final protected function getJsonPayload(): array { return json_decode($this->getRequest()->getContent(), true); }

	final protected function getJsonParamBag(): ParameterBag {
		$data = json_decode($this->getRequest()->getContent(), true);
		$data = is_array($data) ? $data : [];
		return new ParameterBag($data);
	}
}