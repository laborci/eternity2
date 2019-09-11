<?php namespace Eternity2\Module\Codex\Codex\Dictionary;

class Dictionary implements DictionaryInterface{
	protected $dictionary;
	public function __construct($dictionary){
		$this->dictionary = $dictionary;
	}
	public function __invoke($key):string{
		if(!is_string($key) || !is_numeric($key)) return '';
		return array_key_exists($key, $this->dictionary) ? $this->dictionary[$key] : $key;
	}
	public function getDictionary():array{
		return $this->dictionary;
	}
}