<?php namespace Eternity2\Module\Codex\Codex\Dictionary;

interface DictionaryInterface{
	public function __invoke($key):string;
	public function getDictionary():array;
}