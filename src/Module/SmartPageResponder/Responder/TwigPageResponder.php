<?php namespace Eternity2\Module\SmartPageResponder\Responder;

use Eternity2\System\AnnotationReader\AnnotationReader;
use Eternity2\System\ServiceManager\ServiceContainer;
use Eternity2\Module\SmartPageResponder\Twigger\Twigger;
use Eternity2\Mission\Web\Responder\PageResponder;
use Minime\Annotations\Interfaces\AnnotationsBagInterface;

abstract class TwigPageResponder extends PageResponder{

	/** @var AnnotationsBagInterface */
	protected $annotations;
	protected $template;

	public function __construct(){
		/** @var AnnotationReader $annotationReader */
		$annotationReader = ServiceContainer::get(AnnotationReader::class);
		$this->annotations = $annotationReader->getClassAnnotations(get_called_class());
		$this->template = $this->annotations->get('template');
	}
	protected function respond(): string{ return Twigger::Service()->render($this->template, $this->createViewModel()); }
	protected function createViewModel(){ return $this->getDataBag()->all(); }

}