<?php namespace Eternity2\Mission\Web\Pipeline;

use Eternity2\System\ServiceManager\ServiceContainer;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PipelineRunner {

	private $request;
	private $response;
	private $pathBag;
	private $dataBag;
	private $pipeline;

	public function __construct(Request $request,
	                            ParameterBag $pathBag,
	                            ParameterBag $dataBag,
	                            array &$pipeline) {
		$this->request = $request;
		$this->pathBag = $pathBag;
		$this->dataBag = $dataBag;
		$this->pipeline = $pipeline;
		$this->response = new Response();
	}

	public function __invoke($responderClass = null, $arguments = []) {
		$segment = null;
		if (!is_null($responderClass)) {
			$segment = ['responderClass' => $responderClass, 'arguments' => $arguments];
			$this->pipeline = [];
		} else if (count($this->pipeline)) {
			$segment = array_shift($this->pipeline);
		}
		if ($segment) {
			$class = is_array($segment['responderClass']) ? $segment['responderClass'][0] : $segment['responderClass'];
			$method = is_array($segment['responderClass']) ? $segment['responderClass'][1] : null;
			$arguments = $segment['arguments'];

			/** @var Segment $segmentObject */
			$segmentObject = ServiceContainer::get($class);
			$segmentObject->execute(
				$method,
				new ParameterBag($arguments),
				$this);
		}
		return $this->response;
	}

	public function getResponse(): Response { return $this->response; }
	public function setResponse(Response $response) { return $this->response = $response; }
	public function getRequest(): Request { return $this->request; }
	public function getDataBag() { return $this->dataBag; }
	public function getPathBag() { return $this->pathBag; }

}