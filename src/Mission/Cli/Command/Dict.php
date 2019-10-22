<?php namespace Eternity2\Mission\Cli\Command;

use Eternity2\System\VhostGenerator\VhostGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class Dict extends Command{
	protected function configure(){
		$this
			->setName('dictionary-builder')
			->setAliases(['dict'])
			->setDescription('Generates dictionary files')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		$style = new SymfonyStyle($input, $output);
		$indir = env('dict.source');

		$files = glob($indir . '*.yml');
		foreach ($files as $file) if (is_file($file)){
			$data = Yaml::parseFile($file);

			if (array_key_exists('id', $data)){

				if (!array_key_exists('id', $data)) throw new \Exception($file . ' dictionary does not contains id property');
				if (!array_key_exists('dictionary', $data)) throw new \Exception($file . ' dictionary does not contains dictionary property');

				$id = $data['id'];

				if (array_key_exists('languages', $data)){
					foreach ($data['languages'] as $lang=>$language){
						$autovalue = array_key_exists('autovalue', $language) ? $language['autovalue'] : false;
						$output = [];
						if (array_key_exists('php', $language)) $output['php'] = $language['php'];
						if (array_key_exists('jsmodule', $language)) $output['jsmodule'] = $language['jsmodule'];
						if (array_key_exists('json', $language)) $output['json'] = $language['json'];
						$dict = array_map(function($value) use ($lang){ return array_key_exists($lang, $value) ? $value[$lang] : null; }, $data['dictionary']);
						$this->createDictionary($id, $output, $autovalue, $dict);
					}
				}else{
					$autovalue = array_key_exists('autovalue', $data) ? $data['autovalue'] : false;
					$output = [];
					if (array_key_exists('php', $data)) $output['php'] = $data['php'];
					if (array_key_exists('jsmodule', $data)) $output['jsmodule'] = $data['jsmodule'];
					if (array_key_exists('json', $data)) $output['json'] = $data['json'];
					$this->createDictionary($id, $output, $autovalue, $data['dictionary']);
				}
			}
		}

		$style->success('Done');
	}

	protected function createDictionary($id, $output, $autovalue, $dictionary){
		foreach ($dictionary as $key => $value){
			$oldkey = $key;
			$key = strtoupper(str_replace(['-', '.'], '_', $key));
			if(substr($key,0,1) === '~'){
				$key = substr($key, 1);
				$value = env($value);
			}
			$dictionary[$key] = $value;
			unset($dictionary[$oldkey]);
		}
		if ($autovalue){
			foreach ($dictionary as $key => $value){
				if (is_null($value)) $dictionary[$key] = str_replace("{{key}}", $key, str_replace('{{id}}', $id, $autovalue));
			}
		}

		$phpoutdir = env('dict.php.output');
		$jsonoutdir = env('dict.json.output');
		$jsmoduleoutdir = env('dict.jsmodule.output');
		$namespace = env('dict.php.namespace');


		foreach ($output as $kind => $filename){
			switch ($kind){
				case 'php':
					$file = '<?php namespace ' . $namespace . ';' . "\n" .'interface ' . $filename . '{' . "\n";
					foreach ($dictionary as $key => $value){
						$file .= "\tconst " . $key . ' = ' . var_export($value, true) . ';' . "\n";
					}
					$file .= '}';
					file_put_contents($phpoutdir . $filename . '.php', $file);
					break;
				case 'json':
					file_put_contents($jsonoutdir . $filename . '.json', json_encode($dictionary, JSON_PRETTY_PRINT));
					break;
				case 'jsmodule':
					$file = 'let ' . $filename . ' = ' . json_encode($dictionary, JSON_PRETTY_PRINT) . ';' . "\n" . "export default " . $filename . ";";
					file_put_contents($jsmoduleoutdir . $filename . '.js', $file);
					break;
			}
		}
	}

}
