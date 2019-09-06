<?php namespace Eternity2\DBAccess\Filter;

use Eternity2\DBAccess\PDOConnection\AbstractPDOConnection;

abstract class AbstractFilterBuilder {

	protected $connection;

	public function __construct(AbstractPDOConnection $connection) { $this->connection = $connection; }
	abstract public function getSql(array $where): string;
	abstract protected function getSqlFromArray(array $filter): string;

}