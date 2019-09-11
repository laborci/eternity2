<?php namespace Eternity2\Module\Codex\Action;

use Eternity2\Module\Codex\Codex\AdminDescriptor;

class CodexGetForm extends Responder{

	protected function getRequiredPermissionType(): ?string{ return AdminDescriptor::PERMISSION; }

	protected function codexRespond(): ?array{

		$formHandler = $this->adminDescriptor->getFormHandler();

		return [
			'descriptor'=>$formHandler
		];
	}

}

