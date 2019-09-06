<?php namespace Eternity2\Ghost;

use Valentine\Date;

class Field{

	const TYPE_BOOL = 'bool';
	const TYPE_STRING = 'string';
	const TYPE_INT = 'int';
	const TYPE_ID = 'id';
	const TYPE_DATE = 'date';
	const TYPE_DATETIME = 'datetime';
	const TYPE_ENUM = 'enum';
	const TYPE_SET = 'set';
	const TYPE_FLOAT = 'float';
	const TYPE_JSON = 'json';

	public $name;
	public $type;
	public $protected = false;
	public $getter = null;
	public $setter = null;
	private $data;

	public function __construct($name, $type, $data = null){
		$this->name = $name;
		$this->type = $type;
		$this->data = $data;
	}

	public function protect($getter, $setter){
		$this->protected = true;
		$this->getter = $getter;
		$this->setter = $setter;
	}

	public function compose($value){
		if ($value === null) return null;
		switch ($this->type){
			case self::TYPE_DATE:
				return new Date($value);
			case self::TYPE_DATETIME:
				return new \DateTime($value);
			case self::TYPE_INT:
				return intval($value);
			case self::TYPE_ID:
				return intval($value) > 0 ? intval($value) : null;
			case self::TYPE_FLOAT:
				return floatval($value);
			case self::TYPE_BOOL:
				return (bool)$value;
			case self::TYPE_SET:
				return !$value ? [] : explode(',', $value);
			case self::TYPE_JSON:
				return json_decode($value, true);
		}
		return $value;
	}

	public function decompose($value){
		if ($value === null) return null;
		switch ($this->type){
			case self::TYPE_DATE:
				return (function (Date $date){ return $date->format('Y-m-d'); })($value);
			case self::TYPE_DATETIME:
				return (function (\DateTime $date){ return $date->format('Y-m-d H:i:s'); })($value);
			case self::TYPE_INT:
				return intval($value);
			case self::TYPE_ID:
				return intval($value) > 0 ? intval($value) : null;
			case self::TYPE_FLOAT:
				return floatval($value);
			case self::TYPE_BOOL:
				return (int)((bool)$value);
			case self::TYPE_SET:
				return join(',', $value);
			case self::TYPE_JSON:
				return json_encode($value);
		}
		return $value;
	}

	public function import($value){
		if ($value === null || $this->setter === false) return null;
		switch ($this->type){
			case self::TYPE_DATE:
				return new Date($value);
			case self::TYPE_DATETIME:
				return \DateTime::createFromFormat(\DateTime::ISO8601, $value);
			case self::TYPE_INT:
				return intval($value);
			case self::TYPE_ID:
				return intval($value) > 0 ? intval($value) : null;
			case self::TYPE_FLOAT:
				return floatval($value);
			case self::TYPE_BOOL:
				return (bool)$value;
			case self::TYPE_SET:
				return !$value ? [] : explode(',', $value);
			case self::TYPE_JSON:
				return json_decode($value, true);
		}
		return $value;
	}

	public function export($value){
		if ($value === null || $this->getter === false) return null;
		switch ($this->type){
			case self::TYPE_DATE:
				return (function (Date $date){ return $date->format(\DateTime::ISO8601); })($value);
			case self::TYPE_DATETIME:
				return (function (\DateTime $date){ return $date->format(\DateTime::ISO8601); })($value);
			case self::TYPE_BOOL:
				return (bool)$value;
			case self::TYPE_INT:
				return intval($value);
			case self::TYPE_FLOAT:
				return floatval($value);
			case self::TYPE_SET:
				return $value;
			case self::TYPE_JSON:
				return $value;
		}
		return $value;
	}
}