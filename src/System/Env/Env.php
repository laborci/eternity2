<?php namespace Eternity2\System\Env;

use Eternity2\System\ServiceManager\Service;
use Eternity2\System\ServiceManager\SharedService;

class Env implements SharedService {

	use Service;

	protected $env = [];

	public function store($env) {$this->env = $env;}
	public function get($key = null) { return $key === null ? $this->env : DotArray::get($this->env, $key, null); }
	public function set($key, $value) { DotArray::set($this->env, $key, $value); }
	static public function loadFacades() { include "facades.php"; }

}