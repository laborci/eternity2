<?php namespace Eternity2\System\ServiceManager;


class ServiceFactory{

	protected $name;
	protected $shared = false;
	protected $factory = null;
	protected $service = null;
	protected $type;
	protected $sharedService = null;

	const SERVICE = 1;
	const FACTORY = 2;
	const FACTORY_STATIC = 3;
	const FACTORY_SERVICE = 4;

	public function __construct(string $name) {
		$this->name = $name;
	}

	public function shared(){
		$this->shared = true;
		return $this;
	}

	public function factory(callable $factory){
		$this->type = static::FACTORY;
		$this->factory = $factory;
		return $this;
	}

	public function factoryStatic(array $factory){
		$this->type = static::FACTORY_STATIC;
		$this->factory = $factory;
		return $this;
	}

	public function factoryService(array $factory){
		$this->type = static::FACTORY_SERVICE;
		$this->factory = $factory;
		return $this;
	}

	public function service($service){
		$this->type = static::SERVICE;
		$this->service = $service;
		return $this;
	}

	public function get(){
		if(!is_null($this->sharedService)){
			return $this->sharedService;
		}elseif ($this->type === static::FACTORY || $this->type === static::FACTORY_STATIC){
			$service = ($this->factory)($this->name);
		}elseif ($this->type === static::FACTORY_SERVICE){
			$method = $this->factory[1];
			$service = ServiceContainer::get($this->factory[0])->$method($this->name);
		}else{
			$class = $this->service;
			$reflect = new \ReflectionClass($class);
			$constructor = $reflect->getConstructor();
			$arguments = [];

			if(!is_null($constructor)) {
				$parameters = $constructor->getParameters();
				foreach ($parameters as $parameter) {
					$arguments[] = ServiceContainer::get(strval($parameter->getType()));
				}
			}
			$service = new $class(...$arguments);
		}

		if($this->shared){
			$this->sharedService = $service;
		}

		return $service;
	}

}