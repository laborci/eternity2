<?php namespace Eternity2\Attachment;

use Eternity2\Thumbnail\Thumbnail;
use JsonSerializable;
use name;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @property-read           $url
 * @property-read           $path
 * @property-read           $category
 * @property-read Thumbnail $thumbnail
 */
class Attachment extends File implements JsonSerializable{

	/** @var AttachmentCategoryManager */
	private $categoryManager;
	public $description;
	public $ordinal;
	public $meta;

	public function __construct(
		$filename,
		AttachmentCategoryManager $categoryManager,
		$description = '',
		$ordinal = 0,
		$meta = []
	){
		parent::__construct($categoryManager->getPath() . '/' . $filename);
		$this->categoryManager = $categoryManager;
		$this->description = $description;
		$this->ordinal = $ordinal;
		$this->meta = $meta;
	}

	public function getCategory(): AttachmentCategory{ return $this->categoryManager->getCategory(); }

	public function __get($name){
		switch ($name){
			case 'path':
				return $this->categoryManager->getPath() . $this->getFilename();
			case 'url':
				return $this->categoryManager->getUrl() . $this->getFilename();
			case 'category':
				return $this->getCategory()->getName();
			case 'thumbnail':
				return new Thumbnail($this);
		}
		return null;
	}

	public function getRecord(){
		return [
			'path'        => $this->categoryManager->getOwner()->getPath(),
			'url'         => $this->url,
			'file'        => $this->getFilename(),
			'size'        => $this->getSize(),
			'meta'        => $this->meta,
			'description' => $this->description,
			'ordinal'     => $this->ordinal,
			'category'    => $this->categoryManager->getCategory()->getName(),
			'extension'   => strtolower($this->getExtension()),
			'mime-type'   => $this->getMimeType(),
		];
	}

	public function store(){ $this->categoryManager->store($this); }
	public function remove(){ $this->categoryManager->remove($this); }

	/**
	 * Specify data which should be serialized to JSON
	 * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize(){
		return $this->getRecord();
	}
}