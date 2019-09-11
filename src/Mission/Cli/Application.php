<?php namespace Eternity2\Mission\Cli;

use Eternity2\System\Mission\Mission;
use Eternity2\System\ServiceManager\SharedService;

class Application implements Mission, SharedService{

	/** @var \Symfony\Component\Console\Application */
	protected $application;

	private function addCommands(){
		$commands = $this->env['commands'];
		foreach ($commands as $command){
			$this->application->add(new $command());
		}
	}

	protected function addCustomCommands(){ }

	protected $env;

	public function run($env){
		$this->env = $env;
		$this->application = new \Symfony\Component\Console\Application('plx', '2');
		$this->addCommands();
		$this->addCustomCommands();
		$this->application->run();
	}
}