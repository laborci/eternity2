<?php namespace Eternity2\Mission\Cli\Command;

use Eternity2\DBAccess\ConnectionFactory;
use Eternity2\System\Env\EnvLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Migrate extends Command{

	/** @var SymfonyStyle */
	protected $style;

	/** @var \Eternity2\DBAccess\SmartAccess\AbstractSmartAccess */
	protected $smart;

	protected function configure(){
		$this->setName('migrate');
		$this->addOption("create", "c", InputOption::VALUE_NONE, "Create migration");
		//$this->addOption("apply", "d", InputOption::VALUE_NONE, "Dump data");
		$this->addOption("database", "db", InputOption::VALUE_REQUIRED, "Database name", 'default');
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$this->style = new SymfonyStyle($input, $output);

		$database = env('database')[$input->getOption('database')];

		$this->connection = ConnectionFactory::factory($database);
		$this->smart = $this->connection->createSmartAccess();

		$version = $this->getMigrationVersion();
		$tables = $this->getTables();

		if ($input->getOption('create') !== false){

			$migrations = [];

			foreach ($tables as $table){
				$migrations[] = $this->createMigration($table);
			}
		}

	}

	protected function createMigration($tablename){
		$table = $this->getTableDetails($tablename);
		if(!$table['details']['Comment']){

		}else{

		}
	}


	protected function nameTable($tablename){
		$uid = uniqid('', true);
		$this->smart->query("ALTER TABLE `$tablename` COMMENT = '$uid'");
		return $uid;
	}

	protected function nameField($tablename, $field){
		$uid = uniqid('', true);
		$this->smart->query("ALTER TABLE `$tablename` COMMENT = '$uid'");
		return $uid;
	}

	protected function createAlterField($fieldData){
		return 'CHANGE'
	}
	
	
	protected function getTables(){
		$tables = $this->smart->getValues('SHOW TABLES');
		return array_filter($tables, function ($item){ return $item !== '@migration'; });
	}
	protected function getTableDetails($table){
		$info = [];
		$info['details'] = $this->smart->getRows("SHOW TABLE STATUS WHERE Name='" . $table . "'")[0];
		$info['fields'] = $this->smart->getRows("SHOW FULL COLUMNS FROM `blogpost`");
		$info['fks'] = $this->smart->getRows("SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE	REFERENCED_TABLE_SCHEMA = '" . $this->smart->getDatabase() . "' AND TABLE_NAME = '" . $table . "'");
		$info['keys'] = $this->smart->getRows("SHOW INDEXES IN `".$table."`");
		return $info;
	}
	protected function getMigrationVersion(){
		$exists = $this->smart->tableExists('@migration');
		if (!$exists){
			$this->smart->query("CREATE TABLE `@migration` (`version` int(11) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;");
			$this->smart->insert('@migration', ['version' => 0]);
		}
		return $this->smart->getValue("SELECT version FROM `@migration`");
	}

}
