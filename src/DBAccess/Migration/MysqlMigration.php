<?php namespace RedFox\Database\Migration;


use RedFox\Database\SmartAccess\MysqlSmartAccess;

class MysqlMigration {

	protected $access;
	protected $charset = 'utf8';
	protected $collation = 'utf8_hungarian_ci';
	protected $engine = 'InnoDB';

	public function __construct(MysqlSmartAccess $access) {
		$this->access = $access;
	}

	public function setDefaults($charset, $collation, $engine) {
		$this->charset = $charset;
		$this->collation = $collation;
		$this->engine = $engine;
	}

	public function createTable($name, $engine = null, $charset = null, $collation = null) {
		$collation = is_null($collation) ? $this->collation : $collation;
		$charset = is_null($charset) ? $this->charset : $charset;
		$engine = is_null($engine) ? $this->engine : $engine;

		$sql = <<<"SQLEND"
			CREATE TABLE `$name` (
			`id` int(11) unsigned NOT null AUTO_INCREMENT,
			  PRIMARY KEY(`id`)
			) ENGINE = $engine AUTO_INCREMENT = 1 CHARSET = $charset COLLATE = $collation
SQLEND;
		$this->access->query($sql);
	}

	public function renameTable($name, $newname){
		$sql = "RENAME TABLE `$name` TO `$newname`";
		$this->access->query($sql);
	}

	public function alterTableEngine($name, $engine){
		$sql = "ALTER TABLE `$name` ENGINE = $engine";
		$this->access->query($sql);
	}

	public function addField($table, $name, $type, $length, bool $null=true, $default='NULL', $unsigned = false, $comment=null){
		$null = $null ? 'NULL' : 'NOT NULL';
		$unsigned = $unsigned ? 'UNSIGNED' : '';
		if(is_array($length)){ $length = "'".join("','", $length)."'"; }
		if(!is_null($comment)) $comment = "COMMENT '$comment'";
		if(!is_null($default)) $default = 'NULL';
		$sql = " ALTER TABLE `$table` ADD `$name` $type($length) $unsigned  $null  DEFAULT $default $comment";
		$this->access->query($sql);
	}

	public function changeField($table, $name, $newname, $type, $length, bool $null=true, $default='NULL', $unsigned = false, $comment=null){
		$null = $null ? 'NULL' : 'NOT NULL';
		$unsigned = $unsigned ? 'UNSIGNED' : '';
		if(is_null($newname)) $newname = $name;
		if(is_array($length)){ $length = "'".join("','", $length)."'"; }
		if(!is_null($comment)) $comment = "COMMENT '$comment'";
		if(is_null($default)) $default = 'NULL';
		$sql = " ALTER TABLE `$table` CHANGE `$name` `$newname` $type($length) $unsigned  $null  DEFAULT $default $comment";
		$this->access->query($sql);
	}

}