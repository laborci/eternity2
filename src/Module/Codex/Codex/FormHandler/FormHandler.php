<?php namespace Eternity2\Module\Codex\Codex\FormHandler;

use Eternity2\Module\Codex\Codex\AdminDescriptor;
use Eternity2\Module\Codex\Codex\FormHandler\FormSection;
use Eternity2\Module\Codex\Codex\ItemConverterInterface;
use Eternity2\Module\Codex\Codex\ItemDataImporterInterface;
use JsonSerializable;
class FormHandler implements JsonSerializable{

	/** @var AdminDescriptor */
	protected $admin;

	/** @var \Eternity2\Module\Codex\Codex\DataProvider\DataProviderInterface */
	protected $dataProvider;

	/** @var ItemConverterInterface */
	private $itemConverter;

	/** @var \Eternity2\Module\Codex\Codex\ItemDataImporterInterface */
	private $itemDataImporter;

	/** @var \Eternity2\Module\Codex\Codex\FormHandler\FormSection[] */
	protected $sections;

	protected $JSplugins = [];
	protected $labelField = null;

	protected $attachmentCategories = [];

	public function __construct(AdminDescriptor $admin){
		$this->admin = $admin;
		$this->dataProvider = $admin->getDataProvider();
		$this->itemConverter = $this->dataProvider;
		$this->itemDataImporter = $this->dataProvider;
	}

	public function addAttachmentCategory($category, $label){
		$this->attachmentCategories[$category] = $label;
	}

	public function addJSPlugin($plugin){ $this->JSplugins[] = $plugin; }
	public function setLabelField($field){ $this->labelField = $field; }
	public function setItemConverter(ItemConverterInterface $itemConverter){ $this->itemConverter = $itemConverter; }
	public function setItemDataImporter(ItemDataImporterInterface $itemDataImporter){ $this->itemDataImporter = $itemDataImporter; }

	public function section($label){
		$section = new FormSection($label, $this->admin);
		$this->sections[] = $section;
		return $section;
	}

	public function jsonSerialize(){
		return [
			'sections'             => $this->sections,
			'plugins'              => $this->JSplugins,
			'labelField'           => $this->labelField,
			'tabIcon'              => $this->admin->getTabIcon(),
			'formIcon'             => $this->admin->getFormIcon(),
			'attachmentCategories' => $this->attachmentCategories,
		];
	}

	public function get($id = null){
		if (is_null($id)) $item = $this->dataProvider->getNewItem();
		else $item = $this->dataProvider->getItem($id);
		if (is_null($item)) return null;
		$row = $this->itemConverter->convertItem($item);
		$data = [];
		foreach ($this->sections as $section) foreach ($section->getInputs() as $input){
			$data[$input->getField()] = array_key_exists($input->getField(), $row) ? $row[$input->getField()] : null;
		}
		return [
			"id"     => $id,
			"fields" => $data,
		];
	}

	public function save($id, $data){
		if (is_numeric($id) && $id > 0){
			$newid = $this->dataProvider->updateItem($id, $data, $this->itemDataImporter);
		}else{
			$newid = $this->dataProvider->createItem($data, $this->itemDataImporter);
		}
		return $newid;
	}

	public function delete($id){ return $this->dataProvider->deleteItem($id); }
	public function getNew(){ return $this->get(); }

	public function uploadAttachment($id, $category, $file){ return $this->dataProvider->uploadAttachment($id, $category, $file); }
	public function getAttachments($id){ return $this->dataProvider->getAttachments($id); }
	public function copyAttachment($id, $file, $source, $target){ return $this->dataProvider->copyAttachment($id, $file, $source, $target); }
	public function moveAttachment($id, $file, $source, $target){ return $this->dataProvider->moveAttachment($id, $file, $source, $target); }
}
