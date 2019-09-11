<?php namespace Eternity2\Zuul;

use Eternity2\System\Session\Session;

class AuthSession extends Session implements AuthSessionInterface {

	public $userId;
	public function setUserId($userId) { $this->userId = $userId; }
	public function getUserId() { return $this->userId; }

}