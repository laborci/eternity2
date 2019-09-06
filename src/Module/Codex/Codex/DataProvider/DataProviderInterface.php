<?php namespace Eternity2\Module\Codex\Codex\DataProvider;

use Eternity2\Module\Codex\Codex\FilterCreatorInterface;
use Eternity2\Module\Codex\Codex\ItemConverterInterface;
use Eternity2\Module\Codex\Codex\ItemDataImporterInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface DataProviderInterface extends ItemDataImporterInterface, ItemConverterInterface, FilterCreatorInterface{

	public function getList($page, $sorting, $filter, $pageSize, &$count):array ;
	public function getItem($id);
	public function getNewItem();
	public function deleteItem($id);
	public function updateItem($id, array $data, ItemDataImporterInterface $itemDataImporter);
	public function createItem(array $data, ItemDataImporterInterface $itemDataImporter);
	public function uploadAttachment($id, $category, UploadedFile $file);
	public function getAttachments($id):array;

}