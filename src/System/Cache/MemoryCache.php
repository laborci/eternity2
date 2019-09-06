<?php namespace Eternity2\System\Cache;


class MemoryCache {

	private $cache = [];

	public function add($object, $id) { $this->cache[$id] = $object; }
	public function get($id) { return array_key_exists($id, $this->cache) ? $this->cache[$id] : null; }
	public function delete($id) { unset($this->cache[$id]); }
	public function clear(){$this->cache = [];}

}