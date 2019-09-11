<?php namespace Eternity2\GMark;

use Eternity2\System\AnnotationReader\AnnotationReader;

abstract class GMark {

	private $commands = [];

	private $defaultBlockMethod;

	public function __construct(AnnotationReader $annotationReader) {
		$this->annotations = $annotationReader->getClassAnnotations(get_called_class());

		$reflector = new \ReflectionClass($this);
		$methods = $reflector->getMethods();

		foreach ($methods as $method) {
			$annotations = ($annotationReader->getMethodAnnotations(get_called_class(), $method->getName()));

			if ($annotations->has('GMark')) {
				if ($annotations->has('default')) {
					$this->defaultBlockMethod = $method->name;
				} else if ($annotations->has('command')) {
					$attrType = $method->getParameters()[1]->getType()->__toString();
					if ($attrType !== 'array' and $attrType !== 'string') {
						throw new \Exception('GMarkParser ' . $method->name . ' argument $attr type must be string or array, ' . $attrType . ' given.');
					}
					if ($annotations->has('required-attributes')) {
						$requiredAttributes = $annotations->getAsArray('required-attributes');
					} else {
						$requiredAttributes = [];
					}
					$commands = $annotations->getAsArray('command');
					if (is_string($commands)) $commands = [$commands];
					foreach ($commands as $command) {
						$command = trim($command);
						list($command, $as) = array_pad(preg_split('/\s+as\s+/', $command, 2), 2, null);
						$as = $as ? $as : $command;
						$this->commands[$command] = [
							'method'             => $method->name,
							'as'                 => $as,
							'requiredAttributes' => $requiredAttributes,
							'attrType'           => $attrType,
						];
					}
				}
			}
		}
	}

	public function parse($string) {
		$string = preg_replace("/[\r\n]{2,}/", "\n\n", trim($string));
		$blocks = explode("\n\n", $string);
		$output = [];
		foreach ($blocks as $block) $output[] = $this->parseBlock(trim($block));
		return $this->joinBlocks($output);
	}

	protected function joinBlocks($blocks) {
		return join("\n", $blocks);
	}


	private function parseBlock($block) {

		$command = preg_split('/\s+/', $block, 2)[0];


		if (array_key_exists($command, $this->commands)) {
			$command = $this->commands[$command];

			$method = $command['method'];
			list($commandLine, $body) = array_pad(explode("\n", $block, 2), 2, null);
			$attr = trim(array_pad(preg_split('/\s+/', $commandLine, 2), 2, null)[1]);
			if ($command['attrType'] === 'array') {
				try {
					$attr = $this->parseAttributes($attr);
				} catch (\Throwable $exception) {
					return '<error>ATTRIBUTES COULD NOT BE PARSED in line: ' . $commandLine . '</error>';
				}
				foreach ($command['requiredAttributes'] as $requiredAttribute) {
					if (!array_key_exists($requiredAttribute, $attr)) {
						return '<error>ATTRIBUTE ' . $requiredAttribute . ' MISSING in line: ' . $commandLine . '</error>';
					}
				}
			}
			return $this->$method($body ? $body : '', $attr, $command['as']);
		} else if ($this->defaultBlockMethod) {
			$method = $this->defaultBlockMethod;
			return $this->$method($block);
		}
	}

	private function parseAttributes($attributes) {
		$x = (array)new \SimpleXMLElement("<element $attributes />");
		return current($x);
	}
}