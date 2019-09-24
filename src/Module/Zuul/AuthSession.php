<?php namespace Eternity2\Module\Zuul;

use Eternity2\System\Session\Session;

class AuthSession extends Session implements \Eternity2\Module\Zuul\Interfaces\AuthSessionInterface {

	public $userId;
	public function setUserId($userId) { $this->userId = $userId; }
	public function getUserId() { return $this->userId; }

}