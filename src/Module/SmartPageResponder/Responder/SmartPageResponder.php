<?php namespace Eternity2\Module\SmartPageResponder\Responder;

use Eternity2\Mission\Web\ClientVersion\ClientVersion;

abstract class SmartPageResponder extends TwigPageResponder {

	protected $title;
	protected $bodyclass;
	protected $language;


	protected function getViewModelData() { return $this->getDataBag()->all(); }

	protected function createViewModel() {
		return [
			'data'      => $this->getViewModelData(),
			'smartpage' => $this->getViewModelSmartPageComponents(),
		];
	}

	private function getViewModelSmartPageComponents() {
		return [
			'clientversion' => ClientVersion::get(),
			'title'         => $this->title ? $this->title : $this->annotations->get('title'),
			'language'      => $this->language ? $this->language : $this->annotations->get('language', env('LANGUAGE')),
			'bodyclass'     => $this->bodyclass ? $this->bodyclass : $this->annotations->get('bodyclass'),
			'css'           => $this->annotations->getAsArray('css'),
			'js'            => $this->annotations->getAsArray('js'),
		];
	}

}





