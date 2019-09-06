<?php namespace Eternity2\DBAccess\PDOConnection;

use Eternity2\DBAccess\Filter\{AbstractFilterBuilder, MysqlFilterBuilder};
use Eternity2\DBAccess\Finder\{AbstractFinder, MysqlFinder};
use Eternity2\DBAccess\Repository\{AbstractRepository, MysqlRepository};
use Eternity2\DBAccess\SmartAccess\{AbstractSmartAccess, MysqlSmartAccess};

class MysqlPDOConnection extends AbstractPDOConnection {
	public function quoteValue($subject, bool $addQuotationMarks = true): string { return $subject === null ? 'NULL' : ($addQuotationMarks ? $this->quote($subject) : trim($this->quote($subject), "'")); }
	public function quoteArray(array $array, bool $addQuotationMarks = true): array { return array_map(function ($val) use ($addQuotationMarks) { return $this->quote($val, $addQuotationMarks); }, $array); }
	public function escapeSQLEntity($subject): string { return '`' . $subject . '`'; }
	public function escapeSQLEntities(array $array): array { return array_map(function ($val) { return $this->escapeSQLEntity($val); }, $array); }
	public function applySQLParameters(string $sql, array $sqlParams = []): string {
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

	private $filterBuilder;
	private $finder;
	private $smartAccess;
	private $repositories = [];

	public function createFinder(): AbstractFinder { return $this->finder ? $this->finder : ($this->finder = new MysqlFinder($this)); }
	public function createSmartAccess(): AbstractSmartAccess { return $this->smartAccess ? $this->smartAccess : ($this->smartAccess = new MysqlSmartAccess($this)); }
	public function createRepository(string $table): AbstractRepository { return array_key_exists($table, $this->repositories) ? $this->repositories[$table] : ($this->repositories[$table] = new MysqlRepository($this, $table)); }
	public function createFilterBuilder(): AbstractFilterBuilder { return $this->filterBuilder ? $this->filterBuilder : ($this->filterBuilder = new MysqlFilterBuilder($this)); }

}

