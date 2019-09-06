<?php namespace Eternity2\DBAccess\Repository;


class MysqlRepository extends AbstractRepository {

	public function insert(array $record, $insertIgnore = false) {
		$data = [];
		foreach ($record as $key => $value) if ($key != 'id') {
			if (substr($key, 0, 1) === '!') {
				$key = substr($key, 1);
			} else {
				$value = $this->quoteValue($value);
			}
			$data[] = [$this->escapeSQLEntity($key), $value];
		}
		$sql = 'INSERT ' . ($insertIgnore ? 'IGNORE' : '') . ' INTO ' . $this->escTable .
			' (' . join(', ', array_column($data, 0)) . ') ' .
			' VALUE(' . join(', ', array_column($data, 1)) . ')';
		$this->query($sql);
		return $this->connection->lastInsertId();
	}

	public function update($record): int {
		$data = [];
		foreach ($record as $key => $value) if ($key != 'id') {
			if (substr($key, 0, 1) === '!') {
				$key = substr($key, 1);
			} else {
				$value = $this->quoteValue($value);
			}
			$data[] = $this->escapeSQLEntity($key) . '=' . $value;
		}
		$sql = 'UPDATE ' . $this->escTable . ' SET ' . implode(', ', $data) . ' WHERE id=' . $this->quoteValue($record['id']);
		return $this->query($sql)->rowCount();
	}

	public function delete(int $id) { return $this->query("DELETE FROM " . $this->escTable . " WHERE id = " . $this->quoteValue($id))->rowCount(); }
}
