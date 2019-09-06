<?php namespace Eternity2\System\Session;

use Eternity2\System\ServiceManager\SharedService;

abstract class Session implements SharedService {

	private $fields;
	private $namespace;

	function __construct() {
		$this->fields = $this->getFields();
		$this->namespace = $this->getNamespace();
		if (!array_key_exists($this->namespace, $_SESSION)) $this->forget();
		$this->load();
		register_shutdown_function([$this, 'flush']);
	}

	private function load() {
		foreach ($this->fields as $field) {
			$this->$field = array_key_exists($field, $_SESSION[$this->namespace]) ? $_SESSION[$this->namespace][$field] : null;
		}
	}

	public function forget() {
		$_SESSION[$this->namespace] = [];
		foreach ($this->fields as $field) {
			$_SESSION[$this->namespace][$field] = $this->$field = null;
		}
	}

	public function flush() {
		foreach ($this->fields as $field) {
			$_SESSION[$this->namespace][$field] = $this->$field;
		}
	}

	private function getFields() {
		$fields = [];
		$properties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
		foreach ($properties as $property) {
			$fields[] = $property->name;
		}
		return $fields;
	}

	protected function getNamespace() { return static::class; }

}