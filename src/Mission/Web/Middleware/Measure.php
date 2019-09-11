<?php namespace Eternity2\Mission\Web\Middleware;

use Eternity2\Mission\Web\Pipeline\Middleware;

class Measure extends Middleware {

	public function run(){
		$time = microtime(1);
		$this->next();
		dump('runtime: '.(microtime(1)-$time));
	}

}