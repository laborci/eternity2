<?php namespace Eternity2\Module\Codex\Codex;

interface ItemConverterInterface{
	public function convertItem($item):array;
}