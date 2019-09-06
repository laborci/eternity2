<?php namespace Eternity2\Mission\Web\Pipeline;

abstract class Middleware extends Segment {
	final public function __invoke($method = 'run') {
		if (method_exists($this, 'shutDown')) register_shutdown_function([$this, 'shutDown']);
		$this->$method();
	}
}