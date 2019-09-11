<?php namespace Eternity2\DBAccess\Filter;


class MysqlFilterBuilder extends AbstractFilterBuilder {

	public function getSql(array $where): string {
		if (!$where) return null;
		$sql = '';
		foreach ($where as $filterSegment) {
			if ($filterSegment['sql'] instanceof Filter)
				$filterSegment['sql'] = $filterSegment['sql']->getSql($this->connection);
			else if (is_array($filterSegment['sql']))
				$filterSegment['sql'] = $this->getSqlFromArray($filterSegment['sql']);
			if (trim($filterSegment['sql'])) {
				if ($sql) $sql .= " " . $filterSegment['type'] . " ";
				$sql .= "(" . $this->connection->applySQLParameters($filterSegment['sql'], $filterSegment['args']) . ")";
			}
		}
		return $sql;
	}

	protected function getSqlFromArray(array $filter): string {
		if (!$filter) return null;
		$sql = [];
		foreach ($filter as $key => $value) {
			$sql[] = is_array($value) ?
				$this->connection->applySQLParameters(" `" . $key . "` IN ($1) ", $value) :
				$this->connection->applySQLParameters(" `" . $key . "` = $1 ", $value);
		}
		$completeSql = implode(' AND ', $sql);
		return $completeSql;
	}


}