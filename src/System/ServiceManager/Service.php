<?php namespace Eternity2\System\ServiceManager;

trait Service{
	public static function Service(): self{ return ServiceContainer::get(get_called_class()); }
	public static function Invoke(...$args){ return static::Service()(...$args); }
}