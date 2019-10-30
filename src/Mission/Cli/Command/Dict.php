<?php namespace Eternity2\Mission\Cli\Command;

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

		foreach ($output as $kind => $target){
			switch ($kind){
				case 'php':
					$file = '<?php namespace ' . $target['namespace'] . ';' . "\n" .'interface ' . $target['class'] . '{' . "\n";
					foreach ($dictionary as $key => $value){
						$file .= "\tconst " . $key . ' = ' . var_export($value, true) . ';' . "\n";
					}
					$file .= '}';
					if(!is_dir(env('root').'/'.$target['path'])) mkdir(env('root').'/'.$target['path'], 0777, true);
					file_put_contents(env('root').'/'.$target['path'] .'/'. $target['class'] . '.php', $file);
					break;
				case 'json':
					if(!is_dir(env('root').'/'.$target['path'])) mkdir(env('root').'/'.$target['path'], 0777, true);
					file_put_contents(env('root').'/'.$target['path'] .'/'. $target['file'] , json_encode($dictionary, JSON_PRETTY_PRINT));
					break;
				case 'jsmodule':
					$file = 'let ' . $target['name'] . ' = ' . json_encode($dictionary, JSON_PRETTY_PRINT) . ';' . "\n" . "export default " . $target['name'] . ";";
					if(!is_dir(env('root').'/'.$target['path'])) mkdir(env('root').'/'.$target['path'], 0777, true);
					file_put_contents(env('root').'/'.$target['path'] .'/'. $target['file'] , $file);
					break;
			}
		}
	}

}
