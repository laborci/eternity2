<?php namespace Eternity2\DBAccess\Repository;

//TODO: this is mysql specific


use Eternity2\DBAccess\Filter\Filter;
use Eternity2\DBAccess\Finder\AbstractFinder;
use Eternity2\DBAccess\PDOConnection\AbstractPDOConnection;

abstract class AbstractRepository {


	/** @var string */
	protected $table;
	protected $escTable;
	/** @var AbstractPDOConnection */
	protected $connection;

	public function __construct($connection, $table) {
		$this->table = $table;
		$this->connection = $connection;
		$this->escTable = $this->escapeSQLEntity($table);
	}

	public function search(Filter $filter = null): AbstractFinder { return $this->connection->createFinder()->select($this->escTable . '.*')->from($this->escTable)->where($filter); }
	public function pick(int $id) { return $this->search(Filter::where('id = $1', $id))->pick(); }
	public function collect(array $ids) { return $this->search(Filter::where('id IN ($1)', $ids))->collect(); }
	public function count(Filter $filter = null) { return $this->connection->createFinder()->from($this->escTable)->where($filter)->count(); }
	public function save($record) { return $record['id'] ? $this->update($record) : $this->insert($record); }

	abstract public function insert(array $record, $insertIgnore = false);
	abstract public function update($record): int;
	abstract public function delete(int $id);

	public function getTable(): string { return $this->table; }

	protected function quoteValue($value) { return $this->connection->quoteValue($value); }
	protected function escapeSQLEntity($value) { return $this->connection->escapeSQLEntity($value); }
	protected function query($sql) { return $this->connection->query($sql); }

}
