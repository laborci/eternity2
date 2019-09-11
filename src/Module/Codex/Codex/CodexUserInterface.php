<?php namespace Eternity2\Module\Codex\Codex;

use Eternity2\Zuul\AuthenticableInterface;

interface CodexUserInterface extends AuthenticableInterface{
	public function getCodexAvatar();
}