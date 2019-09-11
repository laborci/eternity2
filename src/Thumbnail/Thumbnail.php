<?php namespace Eternity2\Thumbnail;

use Eternity2\Thumbnail\Exception\SourceFileNotFound;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @property string $png
 * @property string $jpg
 * @property string $gif
 * @property string $url
 */
class Thumbnail{
	protected $urlBase;
	protected $path;
	protected $sourcePath;

	/** @var File */
	protected $file;
	protected $operation;
	protected $jpegQuality;
	protected $pathId;

	const CROP_MIDDLE = 0;
	const CROP_START = -1;
	const CROP_END = 1;

	public function __construct(File $file){
		$this->file = $file;
		$this->sourcePath = env('thumbnail.source-path');
		$this->urlBase = env('thumbnail.url');
		$this->path = env('thumbnail.path');
		$this->secret = env('thumbnail.secret');

		if (strpos($file->getPath(), $this->sourcePath) !== 0) throw new SourceFileNotFound();
		$this->pathId = str_replace('/', '-', substr(trim($file->getPath(), '/'), strlen($this->sourcePath)));
	}

	public function purge(){
		$files = glob($this->path . '/' . $this->file->getFilename() . '.*.' . $this->pathId . '.*');
		foreach ($files as $file)
			unlink($file);
	}

	public function scale(int $width, int $height){
		$padding = 1;
		if ($width > 31 || $height > 31){
			$padding = 2;
		}
		if ($width > 1023 || $height > 1023){
			$padding = 3;
		}
		$width = str_pad(base_convert($width, 10, 32), $padding, '0', STR_PAD_LEFT);
		$height = str_pad(base_convert($height, 10, 32), $padding, '0', STR_PAD_LEFT);
		$this->operation = 's' . $width . $height;
		return $this;
	}

	public function crop(int $width, int $height, int $crop = 0){
		$padding = 1;
		if ($width > 31 || $height > 31){
			$padding = 2;
		}
		if ($width > 1023 || $height > 1023){
			$padding = 3;
		}
		$width = str_pad(base_convert($width, 10, 32), $padding, '0', STR_PAD_LEFT);
		$height = str_pad(base_convert($height, 10, 32), $padding, '0', STR_PAD_LEFT);

		$code = 'c';
		if ($crop == static::CROP_END)
			$code = 'c-';
		if ($crop == static::CROP_START)
			$code = 'c_';
		$this->operation = $code . $width . $height;
		return $this;
	}

	public function box(int $width, int $height){
		$padding = 1;
		if ($width > 31 || $height > 31){
			$padding = 2;
		}
		if ($width > 1023 || $height > 1023){
			$padding = 3;
		}
		$width = str_pad(base_convert($width, 10, 32), $padding, '0', STR_PAD_LEFT);
		$height = str_pad(base_convert($height, 10, 32), $padding, '0', STR_PAD_LEFT);

		$this->operation = 'b' . $width . $height;
		return $this;
	}

	public function width(int $width, int $maxHeight = 0, int $crop = 0){
		$padding = 1;
		if ($width > 31 || $maxHeight > 31){
			$padding = 2;
		}
		if ($width > 1023 || $maxHeight > 1023){
			$padding = 3;
		}
		$width = str_pad(base_convert($width, 10, 32), $padding, '0', STR_PAD_LEFT);
		$maxHeight = str_pad(base_convert($maxHeight, 10, 32), $padding, '0', STR_PAD_LEFT);

		$code = 'w';
		if ($crop == static::CROP_END)
			$code = 'w-';
		if ($crop == static::CROP_START)
			$code = 'w_';
		$this->operation = $code . $width . $maxHeight;
		return $this;
	}

	public function height(int $height, int $maxWidth = 0, int $crop = 0){
		$padding = 1;
		if ($height > 31 || $maxWidth > 31){
			$padding = 2;
		}
		if ($height > 1023 || $maxWidth > 1023){
			$padding = 3;
		}
		$height = str_pad(base_convert($height, 10, 32), $padding, '0', STR_PAD_LEFT);
		$maxWidth = str_pad(base_convert($maxWidth, 10, 32), $padding, '0', STR_PAD_LEFT);

		$code = 'h';
		if ($crop == static::CROP_END)
			$code = 'h-';
		if ($crop == static::CROP_START)
			$code = 'h_';
		$this->operation = $code . $height . $maxWidth;
		return $this;
	}

	public function exportGif(){ return $this->thumbnail('gif'); }

	public function exportPng(){ return $this->thumbnail('png'); }

	public function exportJpg(int $quality = 66){
		$this->jpegQuality = $quality;
		return $this->thumbnail('jpg');
	}

	public function export(int $quality = 66){
		$this->jpegQuality = $quality;
		$fileinfo = pathinfo($this->file);
		$ext = strtolower($fileinfo['extension']);
		if ($ext == 'jpeg')
			$ext = 'jpg';
		return $this->thumbnail($ext);
	}

	protected function thumbnail($ext): string{
		$op = $this->operation;
		if ($ext == 'jpg'){
			if ($this->jpegQuality < 0)
				$this->jpegQuality = 0;
			if ($this->jpegQuality > 100)
				$this->jpegQuality = 100;
			$op .= '.' . base_convert(floor($this->jpegQuality / 4), 10, 32);
		}

		$url = $this->file->getFilename() . '.' . $op . '.' . $this->pathId;
		$url = $this->urlBase . '/' . $url . '.' . base_convert(crc32($url . '.' . $ext . $this->secret), 10, 32) . '.' . $ext;

		return $url;
	}

	public function __get($name){
		switch ($name){
			case 'png':
				return $this->exportPng();
				break;
			case 'gif':
				return $this->exportGif();
				break;
			case 'jpg':
				return $this->exportJpg();
				break;
			case 'url':
				return $this->export();
				break;
		}
		return null;
	}

	public function __isset($name){
		return in_array($name, ['png', 'gif', 'jpg', 'url']);
	}
}