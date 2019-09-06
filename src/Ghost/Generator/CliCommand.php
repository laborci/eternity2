<?php namespace Eternity2\Ghost\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CliCommand extends Command {

	/** @var SymfonyStyle */
	protected $output;

	protected function configure() {
		$this
			->setName('ghost')
			->setDescription('Creates ghost or upadtes the whole ghosthouse')
			->addArgument('name', InputArgument::OPTIONAL)
			->addArgument('table', InputArgument::OPTIONAL)
			->addArgument('database', InputArgument::OPTIONAL)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) { Creator::Service()->execute($input, $output, $this->getApplication()); }

}
