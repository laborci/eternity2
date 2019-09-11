<?php namespace Eternity2\Ghost;

use Eternity2\Attachment\AttachmentOwnerInterface;
use Eternity2\Ghost\Exception\InsufficientData;
use JsonSerializable;

/**
 * @property-read int id
 */
abstract class Ghost implements JsonSerializable, AttachmentOwnerInterface{

	use GhostRepositoryFacadeTrait;
	use GhostAttachmentTrait;

	private $deleted;
	protected $id;

	final public function isExists(): bool{ return (bool)$this->id; }
	final public function isDeleted(): bool{ return $this->deleted; }
	function __toString(){ return get_called_class() . ' ' . $this->id; }

#region Model Creation

	private static function model(): ?Model{ return static::$model; }
	private static function setModel(Model $model){ return static::$model = $model; }

	static final public function init(){
		if (static::model() === null){
			$model = static::createModel();
			static::setModel($model);
		}
	}

	abstract static protected function createModel(): Model;

#endregion

#region Magic Methods

	public function __get(string $name){
		$field = array_key_exists($name, static::model()->fields) ? static::model()->fields[$name] : null;
		if ($field){
			if ($field->getter === null){
				return $this->$name;
			}else{
				$getter = $field->getter;
				return $this->$getter();
			}
		}
		$relation = array_key_exists($name, static::model()->relations) ? static::model()->relations[$name] : null;
		if ($relation){
			return $relation->get($this);
		}

		if (static::model()->getAttachmentStorage()->hasCategory($name)){
			return $this->getAttachmentCategoryManager($name);
		}

		return null;
	}

	public function __set($name, $value){
		$field = array_key_exists($name, static::model()->fields) ? static::model()->fields[$name] : null;
		if (!is_null($field) && $field->setter !== false){
			$setter = $field->setter;
			$this->$setter($value);
			return;
		}
	}

	public function __call(string $name, $arguments){
		$relation = array_key_exists($name, static::model()->relations) ? static::model()->relations[$name] : null;
		if ($relation && $relation->type === Relation::TYPE_HASMANY){
			list($order, $limit, $offset) = array_pad($arguments, 3, null);
			return $relation->get($this, $order, $limit, $offset);
		}
		return null;
	}

#endregion

#region Data Packing

	final public function compose($record): Ghost{
		foreach (static::model()->fields as $fieldName => $field){
			if (array_key_exists($fieldName, $record)){
				$this->$fieldName = $field->compose($record[$fieldName]);
			}else{
				throw new InsufficientData(static::model()->table . ' ' . $fieldName);
			}
		}
		return $this;
	}

	final public function decompose(){
		$record = [];
		foreach (static::model()->fields as $fieldName => $field){
			$record[$fieldName] = $field->decompose($this->$fieldName);
		}
		return $record;
	}

	final public function jsonSerialize(){
		return $this->export();
	}

	final public function export(){
		$record = [];
		foreach (static::model()->fields as $fieldName => $field){
			$record[$fieldName] = $field->export($this->$fieldName);
		}
		return $record;
	}

	final public function import($data){
		foreach (static::model()->fields as $fieldName => $field){
			if (array_key_exists($fieldName, $data)){
				$this->$fieldName = $field->import($data[$fieldName]);
			}
		}
		return $this;
	}

#endregion

#region CRUD
	final public function delete(){
		if ($this->isExists()){
			if ($this->on(static::EVENT___BEFORE_DELETE) === false || !static::model()->isMutable()) return false;
			static::model()->repository->delete($this->id);
			$this->deleted = true;
			$this->on(static::EVENT___AFTER_DELETE);
		}
		return true;
	}

	final public function save(){
		if ($this->isExists()){
			return $this->update();
		}else{
			return $this->insert();
		}
	}

	final private function update(){
		if ($this->on(static::EVENT___BEFORE_UPDATE) === false || !static::model()->isMutable()) return false;
		static::model()->repository->update($this);
		$this->on(static::EVENT___AFTER_UPDATE);
		return $this->id;
	}

	final private function insert(){
		if ($this->on(static::EVENT___BEFORE_INSERT) === false || !static::model()->isMutable()) return false;
		$this->id = static::model()->repository->insert($this);
		$this->on(static::EVENT___AFTER_INSERT);
		return $this->id;
	}

#endregion

#region Events
	const EVENT___BEFORE_DELETE = 'before_delete';
	const EVENT___AFTER_DELETE = 'after_delete';
	const EVENT___BEFORE_UPDATE = 'before_update';
	const EVENT___AFTER_UPDATE = 'after_update';
	const EVENT___BEFORE_INSERT = 'before_insert';
	const EVENT___AFTER_INSERT = 'after_insert';
	const EVENT___ATTACHMENT_ADDED = 'attachment_added';

	public function on($event, $data = null){ return true; }
#endregion

}