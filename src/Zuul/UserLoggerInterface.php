<?php namespace Eternity2\Zuul;

Interface UserLoggerInterface {
	public function log($userId, $event, $details = null);
}