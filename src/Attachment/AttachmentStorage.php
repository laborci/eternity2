<?php namespace Eternity2\Attachment;

use Eternity2\Attachment\Exception\CategoryNotFound;
use SQLite3;

class AttachmentStorage{

	/** @var AttachmentCategory[] */
	private $categories = [];

	/** @var SQLite3 */
	private $metaDBConnection;

	private $path;
	private $url;
	private $metaFile;
	private $storage;
	private $basePath;
	private $baseUrl;

	public function __construct($storage){
		$this->basePath = env('attachment.path');
		$this->baseUrl = env('attachment.url');
		$this->path = $this->basePath . '/' . $storage;
		$this->url = $this->baseUrl . '/' . $storage;
		$this->metaFile = env('attachment.meta-db-path') . '/' . $storage . '.sqlite';
		$this->storage = $storage;
	}

	public function addCategory($name){
		$category = new AttachmentCategory($name, $this);
		$this->categories[$category->getName()] = $category;
		return $category;
	}

	public function getBasePath(){ return $this->basePath; }
	public function getBaseUrl(){ return $this->baseUrl; }
	public function getPath(){ return $this->path; }
	public function getUrl(){ return $this->url; }
	public function getCategories(){ return $this->categories; }
	public function getStorageName(){ return $this->storage; }
	public function hasCategory($category){ return array_key_exists($category, $this->categories); }
	public function getCategory($category): AttachmentCategory{
		if (array_key_exists($category, $this->categories))
			return $this->categories[$category];
		else throw new CategoryNotFound();
	}

	public function getMetaDBConnection(){
		if (is_null($this->metaDBConnection)){
			if (!file_exists($this->metaFile)){
				$connection = new SQLite3($this->metaFile);
				$connection->exec("
						begin;
						create table file
						(
							path text,
							file text,
							size int,
							category text,
							description text,
							ordinal int,
							meta text,
							constraint file_pk
								primary key (path, file, category)
						);
						create index path_index on file (path);
						commit;");
				$connection->close();
			}
			$this->metaDBConnection = new SQLite3($this->metaFile);
		}
		return $this->metaDBConnection;
	}

}