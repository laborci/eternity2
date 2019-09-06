<?php namespace Eternity2\System\VhostGenerator;

use Eternity2\System\ServiceManager\ServiceContainer;

class VhostGenerator {

	public function generate() {
		$templates = env('vhost-generator.templates');
		foreach ($templates as $template) {
			$source = $template['source'];
			$target = $template['target'];
			$template = file_get_contents($source);
			$template = str_replace('{{domain}}', env('domain'), $template);
			$template = str_replace('{{root}}', getenv('root'), $template);
			file_put_contents($target, $template);
		}
	}

}