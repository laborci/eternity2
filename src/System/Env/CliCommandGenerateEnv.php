<?php namespace Eternity2\System\Env;

use Eternity2\System\ServiceManager\ServiceContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class CliCommandGenerateEnv extends Command {

	protected function configure() {
		$this
			->setName('generate-env')
			->setAliases(['env'])
			->setDescription('Generates one big env file');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$style = new SymfonyStyle($input, $output);
		(function(EnvGenerator $eg){ $eg->generate();})(ServiceContainer::get(EnvGenerator::class));
		$style->success('Done');
	}

}
