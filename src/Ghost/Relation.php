<?php namespace Eternity2\Ghost;


use Eternity2\DBAccess\Filter\Filter;
class Relation {

	const TYPE_HASMANY = 'hasMany';
	const TYPE_BELONGSTO = 'belongsTo';

	public $name;
	public $type;
	public $descriptor;

	public function __construct($name, $type, $descriptor) {
		$this->name = $name;
		$this->type = $type;
		$this->descriptor = $descriptor;
	}

	public function get(Ghost $object, $order=null, $limit=null, $offset = null){
		/** @var \Eternity2\Ghost\Repository $targetRepository */

		$targetGhost = $this->descriptor['ghost'];
		$targetRepository = $targetGhost::$model->repository;
		$field = $this->descriptor['field'];

		switch ($this->type){
			case self::TYPE_BELONGSTO:
				return $targetRepository->pick($object->$field);
				break;
			case self::TYPE_HASMANY:
				return $targetRepository->search(Filter::where($field.'=$1', $object->id))->orderIf(!is_null($order), $order)->collect($limit, intval($offset));
				break;
		}
		return null;
	}
}