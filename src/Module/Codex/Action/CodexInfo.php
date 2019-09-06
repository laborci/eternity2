<?php namespace Eternity2\Module\Codex\Action;

use Eternity2\Module\Codex\Codex\AdminDescriptor;

class CodexInfo extends Responder{

	protected function getRequiredPermissionType(): ?string{ return AdminDescriptor::PERMISSION; }

	protected function codexRespond(): ?array{
		return [
			'header' => $this->adminDescriptor->getHeader(),
			'urlBase'=> $this->adminDescriptor->getUrlBase(),
			'list'   => $this->adminDescriptor->getListHandler(),
		];
	}

}

