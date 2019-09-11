<?php namespace Eternity2\DBAccess;

interface SqlLogHookInterface{
	public function log($sql);
}