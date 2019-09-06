<?php namespace Eternity2\System\Cache;

class FileCache {

	protected $path;
	protected $ext;
	protected $cachedThisSession = [];

	public function __construct($path, $ext = 'txt') {
		if (!is_dir($path)) mkdir($path);
		$this->ext = '.' . $ext;
		$this->path = $path . '/';
	}

	public function set($key, $value, int $time = null) {
		$this->cachedThisSession[] = $key;
		$file = $this->file($key);
		file_put_contents($file, $value);
		if(!is_null($time)) touch($file, time()+$time);
	}

	public function exists($key) { return file_exists($this->file($key)); }

	public function get($key) { return file_get_contents($this->file($key)); }

	public function delete($key) { if ($this->exists($key)) unlink($this->file($key)); }

	public function isCachedThisSession($key) { return in_array($key, $this->cachedThisSession); }

	public function file($key) { return $this->path . $key . $this->ext; }

	public function find($pattern) {
		$files = glob($this->path . $pattern . $this->ext);
		$items = [];
		foreach ($files as $file) {
			$info = pathinfo($file);
			$items[] = $info['filename'];
		}
		return $items;
	}

	public function clear($pattern = '*') {
		$items = $this->find($pattern);
		foreach ($items as $item) {
			$this->delete($item);
		}
	}

	public function getTime($key) { return filemtime($this->file($key)); }
	public function getAge($key) { return time() - filemtime($this->file($key)); }
	public function isValid($key){ return $this->exists($key) && $this->getAge($key)<0; }

}