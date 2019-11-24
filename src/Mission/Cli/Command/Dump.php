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
		$this->addOption("database", "db", InputOption::VALUE_REQUIRED, "Database name", 'default');
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		$style = new SymfonyStyle($input, $output);

		$dbName = $input->getOption('database');
		$database = env('database')[$dbName];

		$dsn = $database['scheme'] . ':dbname=' . $database['database'] . ';host=' . $database['host'];
		
		$dump = new \Rah\Danpu\Dump();
		$dump
			->dsn($dsn)
			->user($database['user'])
			->pass($database['password'])
			->tmp(env('path.tmp'))
		;

		if ($input->getOption('structure') === false && $input->getOption('data') === false){
			$file = env('path.dev') . 'dump/snapshot.' .$dbName.'.' .time() . '.sql';
			$style->title('dumping snapshot: ' . $file);
			$dump->structure(true)->disableForeignKeyChecks(true)->data(true)->file($file);
			new Export($dump);
			$style->success('done');
		}

		if ($input->getOption('structure') !== false){
			$file = env('path.dev') . 'dump/structure.' .$dbName.'.'. time() . '.sql';
			$style->title('dumping structure: ' . $file);
			$dump->structure(true)->disableForeignKeyChecks(true)->data(false)->file($file);
			new Export($dump);
			$style->success('done');
		}

		if ($input->getOption('data') !== false){
			$file = env('path.dev') . 'dump/data.' .$dbName.'.' .time() . '.sql';
			$style->title('dumping data' . $file);
			$dump->structure(false)->disableForeignKeyChecks(true)->data(true)->file($file);
			new Export($dump);
			$style->success('done');
		}

	}

}
