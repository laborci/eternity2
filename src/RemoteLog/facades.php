<?php

use Eternity2\RemoteLog\RemoteLog;
use Eternity2\System\ServiceManager\ServiceContainer;

function dump(...$messages) {
	$remoteLog = ServiceContainer::get(RemoteLog::class);
	foreach ($messages as $message) {
		(function (RemoteLog $remoteLog, $message) { $remoteLog->dump($message); })($remoteLog, $message);
	}
}