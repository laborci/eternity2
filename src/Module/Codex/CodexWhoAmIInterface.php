<?php namespace Eternity2\Module\Codex;

use Eternity2\Module\Zuul\Interfaces\WhoAmIInterface;

interface CodexWhoAmIInterface extends WhoAmIInterface{

	public function getName():string;
	public function getAvatar():string;

}