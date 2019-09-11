<?php namespace Eternity2\DBAccess\PDOConnection;

use PDO;

abstract class AbstractPDOConnection extends \PDO implements PDOConnectionInterface {

	/** @var \Eternity2\DBAccess\SqlLogHookInterface */
	protected $sqlLogHook;
	public function setSqlLogHook($hook){$this->sqlLogHook = $hook;}

	public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = []) {
		if(!is_null($this->sqlLogHook))$this->sqlLogHook->log($statement);
		return parent::query($statement);
	}

}