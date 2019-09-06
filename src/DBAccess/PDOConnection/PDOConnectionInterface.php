<?php

namespace Eternity2\DBAccess\PDOConnection;

use Eternity2\DBAccess\Filter\AbstractFilterBuilder;
use Eternity2\DBAccess\Finder\AbstractFinder;
use Eternity2\DBAccess\Repository\AbstractRepository;
use Eternity2\DBAccess\SmartAccess\AbstractSmartAccess;
use PDO;

interface PDOConnectionInterface {
	public function quoteValue($subject, bool $addQuotationMarks = true): string;
	public function quoteArray(array $array, bool $addQuotationMarks = true): array;
	public function escapeSQLEntity($subject): string;
	public function escapeSQLEntities(array $array): array;
	public function applySQLParameters(string $sql, array $sqlParams = []): string;
	public function createFinder(): AbstractFinder;
	public function createSmartAccess(): AbstractSmartAccess;
	public function createRepository(string $table): AbstractRepository;
	public function createFilterBuilder(): AbstractFilterBuilder;
	public function setSqlLogHook($hook);
	public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = []);
}