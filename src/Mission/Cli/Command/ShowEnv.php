<?php namespace Eternity2\Mission\Cli\Command;

use Eternity2\System\Env\EnvLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowEnv extends Command{

	protected function configure(){
		$this->setName('showenv');
		$this->setAliases(['se']);
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		$style = new SymfonyStyle($input, $output);
		$arr = EnvLoader::load();
		$env = [];
		foreach ($arr as $key=>$value){
			if(!is_array($value)) $env[] = [$key, $value];
		}
		$table = new Table($output);
		$table
			->setHeaders(['key', 'value'])
			->setRows($env)
		;
		$table->render();
	}

}
