<?php namespace Eternity2\RemoteLog;

use Eternity2\DBAccess\SqlLogHookInterface;

class SqlLogHook implements SqlLogHookInterface{

	/** @var \Eternity2\RemoteLog\RemoteLog */
	private $logger;
	public function __construct(RemoteLog $logger){ $this->logger = $logger; }
	public function log($sql){ $this->logger->sql($sql); }

}