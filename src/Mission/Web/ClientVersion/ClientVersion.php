<?php namespace Eternity2\Mission\Web\ClientVersion;

class ClientVersion{

	static function get(){
		$file = env('web-responder.client-version');
		return file_exists($file) ? file_get_contents($file) : 0;
	}

}