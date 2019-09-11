<?php namespace Eternity2\Module\Codex\Action;

use Eternity2\Mission\Web\Responder\JsonResponder;
use Eternity2\Module\Codex\Codex\AdminDescriptor;
use Eternity2\Module\Codex\Module;
use Eternity2\System\Module\ModuleLoader;

class CodexMenu extends JsonResponder{

	protected function getRequiredPermissionType(): ?string{ return AdminDescriptor::PERMISSION; }

	protected function respond(): ?array{
		/** @var Module $module */
		$module = ModuleLoader::Service()->get(Module::class);
		return $module->getMenu();
	}

}

