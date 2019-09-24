<?php namespace Eternity2\RemoteLog;

use Eternity2\DBAccess\SqlLogHookInterface;
use Eternity2\System\ServiceManager\ServiceContainer;
use Eternity2\System\StartupSequence\BootSequnece;

class RemoteLogBoot implements BootSequnece {
	public function run() {
		RemoteLog::Service()->registerErrorHandlers();
		ServiceContainer::shared(SqlLogHookInterface::class)->service(SqlLogHook::class);
	}
}
