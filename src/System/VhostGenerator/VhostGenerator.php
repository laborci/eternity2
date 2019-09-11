<?php namespace Eternity2\System\VhostGenerator;

use Eternity2\System\ServiceManager\ServiceContainer;

class VhostGenerator {

	public function generate() {
		$templates = env('vhost-generator.templates');
		foreach ($templates as $template) {
			$source = $template['source'];
			$target = $template['target'];
			$template = file_get_contents($source);

			preg_match_all('/\{\{(.*?)\}\}/', $template, $matches);
			$keys = array_unique($matches[1]);
			foreach ($keys as $key) $template = str_replace('{{'.$key.'}}', env($key), $template);
			file_put_contents($target, $template);
		}
	}

}