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

		$dictspace = [];

		$indir = env('dict.source');
		$phpoutdir = env('dict.php.output');
		$jsonoutdir = env('dict.json.output');
		$jsmoduleoutdir = env('dict.jsmodule.output');
		$namespace = env('dict.php.namespace');

		$files = glob($indir . '*.yml');
		foreach ($files as $file) if (is_file($file)){
			$data = Yaml::parseFile($file);
			if (!array_key_exists('class', $data)) throw new \Exception($file . ' dictionary does not contains class property');
			if (!array_key_exists('dictionary', $data)) throw new \Exception($file . ' dictionary does not contains dictionary property');
			if (is_string($data['class'])){
				$class = $data['class'];
				if (!array_key_exists($class, $dictspace)) $dictspace[$class] = [];
				foreach ($data['dictionary'] as $key => $value){
					$key = strtoupper(str_replace(['-', '.'], '_', $key));
					if (is_null($value) && array_key_exists('autovalue', $data)) $value = str_replace("{{key}}", $key, str_replace('{{class}}', $class, $data['autovalue']));
					$dictspace[$class][$key] = $value;
				}
			}else{
				$classes = $data['class'];
				foreach ($data['dictionary'] as $key => $value){
					$key = strtoupper(str_replace(['-', '.'], '_', $key));

					foreach ($classes as $lang => $class){
						if (is_null($value) && array_key_exists('autovalue', $data)) $dictspace[$class][$key] = str_replace("{{key}}", $key, str_replace('{{class}}', $class, $data['autovalue']));
						if (is_array($value)){
							if (!array_key_exists($lang, $value) || $value[$lang] === null){
								if (array_key_exists('autovalue', $data)){
									$dictspace[$class][$key] = str_replace("{{key}}", $key, str_replace('{{class}}', $class, $data['autovalue']));
								}else{
									$dictspace[$class][$key] = null;
								}
							}else{
								$dictspace[$class][$key] = $value[$lang];
							}
						}else{
							$dictspace[$class][$key] = $value;
						}
					}
				}
			}
		}

		if ($phpoutdir){
			foreach ($dictspace as $class => $dict){
				$file = '<?php namespace ' . $namespace . ';' . "\n" .
					'interface ' . $class . '{' . "\n";
				foreach ($dict as $key => $value){
					$file .= "const " . $key . ' = ' . var_export($value, true) . ';' . "\n";
				}
				$file .= '}';
				file_put_contents($phpoutdir . $class . '.php', $file);

			}
		}
		foreach ($dictspace as $class => $dict){

			if ($jsonoutdir){
				file_put_contents($jsonoutdir . $class . '.json', json_encode($dict));
			}
			if ($jsmoduleoutdir){
				$file = 'let ' . $class . ' = ' . json_encode($dict) . ';' . "\n" . "export default " . $class . ";";
				file_put_contents($jsmoduleoutdir . $class . '.js', $file);
			}
		}

		$style->success('Done');
	}

}
