<?php namespace Eternity2\System\StartupSequence;

use Eternity2\RemoteLog\RemoteLog;
use Eternity2\System\Env\Env;
use Eternity2\System\Env\EnvLoader;
use Eternity2\System\ServiceManager\ServiceContainer;
use Symfony\Component\HttpFoundation\Request;

class StartupSequence {

	public function __construct($root, $ini_path = "etc/ini/", $ini_file = 'env', $env_path = "var/", $env_build_file = 'env.php') {

		putenv('root=' . realpath($root) . '/');
		putenv('env-path=' . getenv('root') . $env_path);
		putenv('env-build-file=' . $env_build_file);
		putenv('ini-path=' . getenv('root') . $ini_path);
		putenv('ini-file=' . $ini_file);
		putenv('context=' . (http_response_code() ? 'WEB' : 'CLI'));

		if(!EnvLoader::checkCache()) EnvLoader::save();
		Env::Service()->store(include getenv('env-path') . getenv('env-build-file'));

		setenv('root', getenv('root'));
		setenv('context', getenv('context'));

		if (env('output-buffering')) ob_start();
		date_default_timezone_set(env('timezone'));
		if(getenv('context') === 'WEB') session_start();

		ServiceContainer::shared(Request::class)->factoryStatic([Request::class, 'createFromGlobals']);

		foreach (env('boot-sequence') as $sequence) {
			(function(BootSequnece $sequence){$sequence->run();})(ServiceContainer::get($sequence));
		}
	}
}

