<?php namespace Eternity2\DBAccess\SmartAccess;

use Eternity2\DBAccess\Filter\Filter;
use Eternity2\DBAccess\PDOConnection\MysqlPDOConnection;
use PDO;


abstract class AbstractSmartAccess {
	/** @var \PDO */
	private $connection;
	/** @var string */
	private $database;

	public function __construct(MysqlPDOConnection $connection) {
		$this->connection = $connection;
		$this->database = $connection->query('select database()')->fetchColumn();
	}

	private function execute($sql, ...$sqlParams) {
		$sql = $this->buildSQL($sql, $sqlParams);
		$statement = $this->connection->query($sql);
		return $statement;
	}

	public function getFoundRows() { return $this->getValue('SELECT FOUND_ROWS()'); }
	public function query(string $sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams); }


	public function getValue(string $sql, ...$sqlParams) {
		$row = $this->getRow($sql, ...$sqlParams);
		return $row ? reset($row) : null;
	}
	public function getRow(string $sql, ...$sqlParams) { return $this->getFirstRow($sql . (stripos($sql, ' LIMIT ') === false ? ' LIMIT 1' : ''), ...$sqlParams); }
	protected function getFirstRow(string $sql, ...$sqlParams) { return $this->execute($sql, $sqlParams)->fetch(PDO::FETCH_ASSOC); }
	public function getRowById(string $table, int $id) { return $this->getRow("SELECT * FROM " . $this->escapeSQLEntity($table) . " WHERE id=" . $this->quote($id)); }

	public function getRowsById(string $table, array $ids) { return $this->getRows('SELECT * FROM ' . $this->escapeSQLEntity($table) . ' WHERE  id IN (' . join(',', $this->quoteArray($ids)) . ')'); }
	public function getValues(string $sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams)->fetchAll(PDO::FETCH_COLUMN, 0); }
	public function getRows(string $sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams)->fetchAll(PDO::FETCH_ASSOC); }
	public function getValuesWithKey(string $sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams)->fetchAll(PDO::FETCH_KEY_PAIR); }
	public function getRowsWithKey(string $sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC); }

	#region insert / update / delete
	public function insert(string $table, array $data, bool $ignore = false): int {
		foreach ($data as $key => $value) {
			if ($key[0] === '!') {
				$key = substr($key, 1);
			} else {
				$value = $this->quote($value);
			}
			$data[$key] = [$this->escapeSQLEntity($key), $value];
		}
		$this->execute($ignore === true ? 'INSERT IGNORE ' : 'INSERT ' .
			'INTO ' . $this->escapeSQLEntity($table) . ' ' .
			'(' . implode(', ', array_column($data, 0)) . ') ' .
			'VALUE (' . implode(', ', array_column($data, 1)) . ')'
		);
		return $this->connection->lastInsertId();
	}


	public function update(string $table, Filter $filter, array $data): int {
		foreach ($data as $key => $value) {
			if ($key[0] === '!') {
				$key = substr($key, 1);
			} else {
				$value = $this->quote($value);
			}
			$data[$key] = $this->escapeSQLEntity($key) . '=' . $value;
		}
		return $this->execute("UPDATE " . $this->escapeSQLEntity($table) . " SET " . implode(", ", $data) . ' WHERE ' . $filter->GetSql($this->connection))->rowCount();
	}
	public function updateById(string $table, int $id, array $data): int { return $this->update($table, Filter::where('id=$1', $id), $data); }

	public function delete(string $table, Filter $filter): int { return $this->execute("DELETE FROM " . $this->escapeSQLEntity($table) . " WHERE " . $filter->GetSql($this->connection))->rowCount(); }
	public function deleteById(string $table, int $id): int { return $this->delete($table, Filter::where('id=$1', $id)); }
	#endregion

	#region escape & quote
	public function buildSQL(string $sql, array $sqlParams = []): string {
		if (count($sqlParams)) {
			foreach ($sqlParams as $key => $param) {
				$valueParam = is_array($param) ? join(',', $this->quoteArray($param)) : $this->quote($param);
				$sql = str_replace('$' . ($key + 1), $valueParam, $sql);
				if (!is_array($param)) {
					$sqlEntityParam = $this->escapeSQLEntity($param);
					$sql = str_replace('@' . ($key + 1), $sqlEntityParam, $sql);
				}
			}
		}
		return $sql;
	}

	public function quote($subject, bool $quote = true): string { return $subject === null ? 'NULL' : ($quote ? $this->connection->quote($subject) : trim($this->connection->quote($subject), "'")); }
	public function quoteArray(array $array, bool $quote = true): array { return array_map(function ($val) use ($quote) { return $this->quote($val, $quote); }, $array); }
	public function escapeSQLEntity($subject): string { return '`' . $subject . '`'; }
	public function escapeSQLEntities(array $array): array { return array_map(function ($val) { return $this->escapeSQLEntity($val); }, $array); }
	#endregion

	#region transaction
	public function beginTransaction(): bool { return $this->connection->beginTransaction(); }
	public function commit(): bool { return $this->connection->commit(); }
	public function rollBack(): bool { return $this->connection->rollBack(); }
	public function inTransaction(): bool { return $this->connection->inTransaction(); }
	#endregion

	#region table manipulation
	public function tableExists(string $table): bool { return $this->getFirstRow("SHOW TABLES LIKE '" . $table . "'") ? true : false; }
	public function getTableType(string $table): string { return $this->getFirstRow("SHOW FULL TABLES WHERE Tables_in_" . $this->database . " = $1", $table)['Table_type']; }
	public function renameTable(string $from, string $to): void { $this->execute("RENAME TABLE " . $this->escapeSQLEntity($from) . " TO " . $this->escapeSQLEntity($to)); }
	public function addTable(string $table, string $properties): void { $this->execute("CREATE TABLE " . $this->escapeSQLEntity($table) . " " . $properties); }
	public function deleteTable(string $table): void { $this->execute("DROP TABLE " . $this->escapeSQLEntity($table)); }
	public function addView(string $view, string $select): void { $this->execute("CREATE VIEW " . $this->escapeSQLEntity($view) . " AS " . $select); }
	public function deleteView(string $view): void { $this->execute("DROP VIEW IF EXISTS `" . $view . "`"); }
	#endregion

	#region field manipulation
	public function fieldExists(string $table, string $field): bool { return $this->getFirstRow("SHOW FULL COLUMNS FROM `" . $table . "` WHERE Field = '" . $field . "'") ? true : false; }
	public function addField(string $table, string $field, string $properties): void { $this->execute("ALTER TABLE " . $this->escapeSQLEntity($table) . " ADD " . $this->escapeSQLEntity($field) . " " . $properties); }
	public function deleteField(string $table, string $field): void { $this->execute("ALTER TABLE " . $this->escapeSQLEntity($table) . " DROP " . $this->escapeSQLEntity($field)); }
	public function getFieldList(string $table): array { return array_column($this->getFieldData($table), 'Field'); }
	public function getFieldData(string $table): array { return $this->getRows("SHOW FULL COLUMNS FROM " . $this->escapeSQLEntity($table)); }
	public function getEnumValues(string $table, string $field): array {
		preg_match_all("/'(.*?)'/", $this->getRows("DESCRIBE " . $this->escapeSQLEntity($table) . " " . $this->quote($field))[0]['Type'], $matches);
		return $matches[1];
	}
	#endregion
}