<?php namespace Eternity2\Mission\Cli\Command;

use Eternity2\System\Env\EnvLoader;
use Rah\Danpu\Export;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Dump extends Command{

	protected function configure(){
		$this->setName('dump');
		$this->addOption("structure", "s", InputOption::VALUE_NONE, "Dump structure");
		$this->addOption("data", "d", InputOption::VALUE_NONE, "Dump data");
		$this->addOption("combined", "c", InputOption::VALUE_NONE, "Dump combined");
		$this->addOption("database", "db", InputOption::VALUE_REQUIRED, "Database name", 'default');
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		$style = new SymfonyStyle($input, $output);

		$database = env('database')[$input->getOption('database')];

		$dsn = $database['scheme'] . ':dbname=' . $database['database'] . ';host='.$database['host'];
		echo $dsn;
		$dump = new \Rah\Danpu\Dump();
		$dump
			->dsn($dsn)
			->user($database['user'])
			->pass($database['password'])
			->tmp(env('path.tmp'))
		;

		if ($input->getOption('combined') !== false){
			$style->title('dumping combined: '.env('path.dev') . 'combined.sql');
			$dump->structure(true)->disableForeignKeyChecks(true)->data(true)->file(env('path.dev') . 'database.'.time().'.sql');
			new Export($dump);
			$dump->structure(true)->disableForeignKeyChecks(true)->data(true)->file(env('path.dev') . 'database.sql');
			new Export($dump);
			$style->success('done');
		}

		if ($input->getOption('structure') !== false){
			$style->title('dumping structure: '.env('path.dev') . 'structure.sql');
			$dump->structure(true)->disableForeignKeyChecks(true)->data(false)->file(env('path.dev') . 'structure.'.time().'.sql');
			new Export($dump);
			$dump->structure(true)->disableForeignKeyChecks(true)->data(false)->file(env('path.dev') . 'structure.sql');
			new Export($dump);
			$style->success('done');
		}

		if ($input->getOption('data') !== false){
			$style->title('dumping data');
			$dump->structure(false)->disableForeignKeyChecks(true)->data(true)->file(env('path.dev') . 'data.'.time().'.sql');
			new Export($dump);
			$style->success('done');
		}

	}

}
