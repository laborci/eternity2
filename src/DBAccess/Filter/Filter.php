<?php namespace Eternity2\DBAccess\Filter;


use Eternity2\DBAccess\PDOConnection\AbstractPDOConnection;

class Filter {

	protected function __construct() { }

	protected $where = [];

	const LIKE_STARTSWITH = 1;
	const LIKE_ENDSWITH = 2;
	const LIKE_INSTRING = 3;

	static public function like(string $string, int $mode = self::LIKE_INSTRING): string {
		if ($mode & self::LIKE_STARTSWITH) $string = '%' . $string;
		if ($mode & self::LIKE_ENDSWITH) $string = $string . '%';
		return $string;
	}

	static public function explode(string $string, string $delimeter = ',', bool $trim = true): array {
		$array = explode($delimeter, $string);
		if ($trim) $array = array_map('trim', $array);
		return $array;
	}

	static public function where(string $sql, ...$sqlParams): self {
		$filter = new static();
		return $filter->addWhere('WHERE', $sql, $sqlParams);
	}

	static public function whereIf(bool $condition, string $sql, ...$sqlParams): self {
		$filter = new static();
		return $condition ? $filter->addWhere('WHERE', $sql, $sqlParams) : $filter;
	}

	public function and($sql, ...$sqlParams): self { return $this->addWhere('AND', $sql, $sqlParams); }
	public function or($sql, ...$sqlParams): self { return $this->addWhere('OR', $sql, $sqlParams); }
	public function andIf(bool $condition, $sql, ...$sqlParams): self { return $condition ? $this->addWhere('AND', $sql, $sqlParams) : $this; }
	public function orIf(bool $condition, $sql, ...$sqlParams): self { return $condition ? $this->addWhere('OR', $sql, $sqlParams) : $this; }
	public function andNot($sql, ...$sqlParams): self { return $this->addWhere('AND NOT', $sql, $sqlParams); }
	public function orNot($sql, ...$sqlParams): self { return $this->addWhere('OR NOT', $sql, $sqlParams); }

	protected function addWhere(string $type, $sql, $sqlParams): self {
		if (!$this->where) {
			$type = 'WHERE';
		} else if ($type == 'WHERE') {
			$type = 'AND';
		}
		$this->where[] = ['type' => $type, 'sql' => $sql, 'args' => $sqlParams];
		return $this;
	}

	public function getSql(AbstractPDOConnection $connection): string { return $connection->createFilterBuilder()->getSql($this->where); }
}