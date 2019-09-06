<?php namespace Eternity2\System\ServiceManager;

trait Service {
	public static function Service(...$args):self{
		if(count($args)){
			return ServiceContainer::get(get_called_class())(...$args);
		}
		return ServiceContainer::get(get_called_class());
	}
}