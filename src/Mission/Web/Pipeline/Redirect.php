<?php namespace Eternity2\Mission\Web\Pipeline;

class Redirect extends Segment {

	protected $url;
	protected $status;

	final public function __invoke($method = null) {
		$this->url = $this->getArgumentsBag()->get('url', '/');
		$this->status = $this->getArgumentsBag()->get('status', 302);

		if(!is_null($method)){ $this->$method(); }

		$response = $this->getResponse();
		$response->headers->set('Location', $this->url);
		$response->setStatusCode($this->status);
	}
}