<?php namespace Eternity2\Ghost\Generator;

use CaseHelper\CaseHelperFactory;
use Eternity2\DBAccess\PDOConnection\AbstractPDOConnection;
use Eternity2\Ghost\Field;
use Eternity2\Ghost\Model;
use Eternity2\Ghost\Relation;
use Eternity2\System\ServiceManager\Service;
use Eternity2\System\ServiceManager\ServiceContainer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Creator{

	use Service;
	protected $ghostPath;
	protected $ghostNamespace;

	/** @var SymfonyStyle */
	protected $style;
	/** @var InputInterface */
	protected $input;
	/** @var OutputInterface */
	protected $output;
	/** @var Application */
	protected $application;
	/** @var array|mixed */
	protected $ghosts;

	public function __construct(){
		$this->ghostPath = env('ghost.ghost-path');
		$this->ghostNamespace = env('ghost.ghost-namespace');
		$this->ghosts = env('ghost.ghosts');
	}

	public function execute(InputInterface $input, OutputInterface $output, Application $application){

		$this->application = $application;
		$this->input = $input;
		$this->output = $output;
		$this->style = new SymfonyStyle($input, $output);

		$this->style->title('GHOST CREATOR');

		foreach ($this->ghosts as $name => $properties){
			$database = is_array($properties) && array_key_exists('database', $properties) ? $properties['database'] : env('ghost.default-database');
			$table = is_array($properties) && array_key_exists('table', $properties) ? $properties['table'] : CaseHelperFactory::make(CaseHelperFactory::INPUT_TYPE_CAMEL_CASE)->toSnakeCase($name);

			$this->style->section($name . ' Ghost');
			$this->generateEntity($name, $table, $database);
			$this->generateGhostFromDatabase($name, $table, $database);
			$this->updateGhost($name);

		}
		$this->style->success('done.');
	}

	protected function updateGhost($name){

		$file = "{$this->ghostPath}/{$name}.ghost.php";

		$this->style->writeln("Update Helper");
		$this->style->write("- Open Ghost ({$name}) model");
		$ghostClass = $this->ghostNamespace . '\\' . $name;
		$this->style->writeln(" - [OK]");

		/** @var Model $model */
		$model = $ghostClass::$model;

		$annotations = [];
		$properties = [];
		$getterSetter = [];
		$attachmentConstants = [];

		foreach ($model->fields as $field){

			$type = '';
			switch ($field->type){
				case Field::TYPE_BOOL:
					$type = 'boolean';
					break;
				case Field::TYPE_DATE:
					$type = '\Valentine\Date';
					break;
				case Field::TYPE_DATETIME:
					$type = '\DateTime';
					break;
				case Field::TYPE_ENUM:
				case Field::TYPE_STRING:
					$type = 'string';
					break;
				case Field::TYPE_SET:
					$type = 'array';
					break;
				case Field::TYPE_INT:
				case Field::TYPE_ID:
					$type = 'int';
					break;
				case Field::TYPE_FLOAT:
					$type = 'float';
					break;
			}
			$properties[] = "\t" . "/** @var {$type} {$field->name} */";
			$properties[] = "\t" . ($field->protected ? 'protected' : 'public') . " \${$field->name};";

			if ($field->protected){
				if ($field->setter !== false && $field->getter !== false)
					$annotations[] = " * @property $" . $field->name;
				elseif ($field->getter !== false)
					$annotations[] = " * @property-read $" . $field->name;
				elseif ($field->setter !== false)
					$annotations[] = " * @property-write $" . $field->name;

				if (is_string($field->getter))
					$getterSetter[] = "\t" . 'abstract protected function ' . $field->getter . '();';

				if (is_string($field->setter))
					$getterSetter[] = "\t" . 'abstract protected function ' . $field->setter . '($value);';
			}
		}

		foreach ($model->virtuals as $field){
			if ($field['setter'] !== false && $field['getter'] !== false)
				$annotations[] = " * @property $" . $field['name'];
			elseif ($field['getter'] !== false)
				$annotations[] = " * @property-read $" . $field['name'];
			elseif ($field['setter'] !== false)
				$annotations[] = " * @property-write $" . $field['name'];
			if (is_string($field['getter']))
				$getterSetter[] = "\t" . 'abstract protected function ' . $field['getter'] . '();';
			if (is_string($field['setter']))
				$getterSetter[] = "\t" . 'abstract protected function ' . $field['setter'] . '($value);';
		}

		foreach ($model->getAttachmentStorage()->getCategories() as $category){
			$annotations[] = ' * @property-read AttachmentCategoryManager $' . $category->getName();
			$attachmentConstants[] = "\tconst A_" . strtoupper($category->getName()) . ' = "' . $category->getName() . '";';

		}

		foreach ($model->relations as $relation){
			switch ($relation->type){
				case Relation::TYPE_BELONGSTO:
					$annotations[] = ' * @property-read \\' . $relation->descriptor['ghost'] . ' $' . $relation->name;
					break;
				case Relation::TYPE_HASMANY:
					$annotations[] = ' * @property-read \\' . $relation->descriptor['ghost'] . '[] $' . $relation->name;
					$annotations[] = ' * @method \\' . $relation->descriptor['ghost'] . '[] ' . $relation->name . '($order = null, $limit = null, $offset = null)';
					break;
			}
		}

		$template = file_get_contents($file);
		$template = str_replace('/*ghost-generator-properties*/', join("\n", $properties), $template);
		$template = str_replace(' * ghost-generator-annotations', join("\n", $annotations), $template);
		$template = str_replace('/*ghost-generator-getters-setters*/', join("\n", $getterSetter), $template);
		$template = str_replace('/*attachment-constants*/', join("\n", $attachmentConstants), $template);

		$this->style->write("- {$file}");
		file_put_contents($file, $template);
		$this->style->writeln(" - [OK]");
	}

	protected function generateGhostFromDatabase($name, $table, $database){

		$file = "{$this->ghostPath}/{$name}.ghost.php";

		$this->style->writeln("Connecting to database");
		$this->style->write("- ${database}");
		/** @var AbstractPDOConnection $connection */
		$connection = ServiceContainer::get($database);
		$smartAccess = $connection->createSmartAccess();
		$this->style->writeln(" - [OK]");

		$this->style->writeln("Fetching table information");
		$this->style->write("- ${table}");
		$fields = $smartAccess->getFieldData($table);
		$this->style->writeln(" - [OK]");

		$constants = [];
		$addFields = [];
		$fieldConstants = [];
		foreach ($fields as $field){
			$addFields[] = "\t\t" . '$model->addField("' . $field['Field'] . '", ' . $this->fieldType($field, $field['Field']) . ');';
			$fieldConstants[] = "\t" . 'const F_' . strtoupper($field['Field']) . ' = "' . $field['Field'] . '";';
			if (strpos($field['Type'], 'set') === 0 || strpos($field['Type'], 'enum') === 0){
				$values = $smartAccess->getEnumValues($table, $field['Field']);
				foreach ($values as $value){
					$constants[] = "\t" . 'const ' . strtoupper($field['Field']) . '_' . strtoupper($value) . ' = "' . $value . '";';
				}
			}
		}
		$addFields[] = "\t\t" . '$model->protectField("id");';

		$template = file_get_contents(__DIR__ . '/ghost.txt');

		$template = str_replace('{{name}}', $name, $template);
		$template = str_replace('{{table}}', $table, $template);
		$template = str_replace('{{connectionName}}', $database, $template);
		$template = str_replace('{{namespace}}', $this->ghostNamespace, $template);
		$template = str_replace('{{add-fields}}', join("\n", $addFields), $template);
		$template = str_replace('{{constants}}', join("\n", $constants), $template);
		$template = str_replace('{{fieldConstants}}', join("\n", $fieldConstants), $template);

		$this->style->writeln("Generate Helper from database");
		$this->style->write("- {$file}");
		file_put_contents($file, $template);
		$this->style->writeln(" - [OK]");
	}

	protected function generateEntity($name, $table, $database){
		$this->style->writeln("Generate Ghost");
		$file = "{$this->ghostPath}/{$name}.php";
		$this->style->write("- {$file}");

		if (file_exists($file)){
			$this->style->writeln(" - [ALREADY EXISTS]");
		}else{
			$template = file_get_contents(__DIR__ . '/entity.txt');
			$template = str_replace('{{namespace}}', $this->ghostNamespace, $template);
			$template = str_replace('{{name}}', $name, $template);
			$template = str_replace('{{table}}', $table, $template);
			file_put_contents($file, $template);
			$this->style->writeln(" - [OK]");
		}
	}

	protected function fieldType($db_field, $fieldName){

		$dbtype = $db_field['Type'];

		if ($db_field['Comment'] == 'json') return 'Field::TYPE_JSON';
		if ($dbtype == 'tinyint(1)') return 'Field::TYPE_BOOL';
		if ($dbtype == 'date') return 'Field::TYPE_DATE';
		if ($dbtype == 'datetime') return 'Field::TYPE_DATETIME';
		if ($dbtype == 'float') return 'Field::TYPE_FLOAT';
		if (strpos($dbtype, 'int(11) unsigned') === 0 && (substr($fieldName, -2) == 'Id' || $fieldName == 'id' || $db_field['Comment'] == 'id')) return 'Field::TYPE_ID';
		if (strpos($dbtype, 'int') === 0) return 'Field::TYPE_ID';
		if (strpos($dbtype, 'tinyint') === 0) return 'Field::TYPE_INT';
		if (strpos($dbtype, 'smallint') === 0) return 'Field::TYPE_INT';
		if (strpos($dbtype, 'mediumint') === 0) return 'Field::TYPE_INT';
		if (strpos($dbtype, 'bigint') === 0) return 'Field::TYPE_INT';;

		if (strpos($dbtype, 'varchar') === 0) return 'Field::TYPE_STRING';
		if (strpos($dbtype, 'char') === 0) return 'Field::TYPE_STRING';
		if (strpos($dbtype, 'text') === 0) return 'Field::TYPE_STRING';
		if (strpos($dbtype, 'text') === 0) return 'Field::TYPE_STRING';
		if (strpos($dbtype, 'tinytext') === 0) return 'Field::TYPE_STRING';
		if (strpos($dbtype, 'mediumtext') === 0) return 'Field::TYPE_STRING';
		if (strpos($dbtype, 'longtext') === 0) return 'Field::TYPE_STRING';

		if (strpos($dbtype, 'set') === 0) return 'Field::TYPE_SET';
		if (strpos($dbtype, 'enum') === 0) return 'Field::TYPE_ENUM';
		return '';
	}

	protected function menu($title, $options, $default){ return array_search($this->style->choice($title, array_values($options), $options[$default]), $options); }

}