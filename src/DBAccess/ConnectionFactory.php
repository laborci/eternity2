<?php namespace Eternity2\DBAccess;

use Eternity2\DBAccess\PDOConnection\MysqlPDOConnection;
use Eternity2\DBAccess\PDOConnection\PDOConnectionInterface;
use Eternity2\System\ServiceManager\ServiceContainer;
use PDO;

class ConnectionFactory{

	static function factory($env): PDOConnectionInterface{
		switch ($env['scheme']){
			case 'mysql':
				$connection = static::mysql($env);
				break;
			default:
				$connection = null;
		}

		$sqlHook = ServiceContainer::get(SqlLogHookInterface::class, true);
		if (!is_null($sqlHook)) $connection->setSqlLogHook($sqlHook);

		return $connection;
	}

	static function mysql($env): PDOConnectionInterface{

		$host = $env['host'];
		$database = $env['database'];
		$user = $env['user'];
		$password = $env['password'];
		$port = $env['port'];
		$charset = $env['charset'];

		$dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';port=' . $port . ';charset=' . $charset;

		$connection = new MysqlPDOConnection($dsn, $user, $password);

		$connection->setAttribute(PDO::ATTR_PERSISTENT, true);
		$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$connection->query("SET CHARACTER SET $charset");

		return $connection;
	}

}