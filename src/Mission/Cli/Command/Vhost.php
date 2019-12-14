<?php namespace Eternity2\Mission\Cli\Command;

use Eternity2\System\VhostGenerator\VhostGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class Vhost extends Command {
	protected function configure() {
		$this
			->setName('generate-vhost')
			->setAliases(['vhost'])
			->setDescription('Generates vhost file from the template');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$style = new SymfonyStyle($input, $output);

		$files = env('vhost-generator');
		
		foreach ($files as $name=>$file){
			$source = $file['template'];
			$target = $file['output'];

			$template = file_get_contents($source);
			preg_match_all('/\{\{(.*?)\}\}/', $template, $matches);
			$keys = array_unique($matches[1]);
			foreach ($keys as $key) $template = str_replace('{{' . $key . '}}', env($key), $template);
			file_put_contents($target, $template);
			$style->success($name.' Done');

		}

	}

}
