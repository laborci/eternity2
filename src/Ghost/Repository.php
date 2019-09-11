<?php namespace Eternity2\Ghost;

use Eternity2\DBAccess\Filter\Filter;
use Eternity2\DBAccess\Finder\AbstractFinder;
use Eternity2\System\Cache\MemoryCache;

class Repository{

	protected $ghost;

	/** @var Model */
	protected $model;
	/** @var MemoryCache $cache*/
	protected $cache;
	/** @var \Eternity2\DBAccess\Repository\AbstractRepository $dbRepository */
	protected $dbRepository;

	public function __construct($ghost, Model $model){
		$this->ghost = $ghost;
		$this->model = $model;
		$this->cache = new MemoryCache();
		$this->dbRepository = $model->connection->createRepository($model->table);
	}
	private function addToCache(Ghost $object):Ghost{ $this->cache->add($object, $object->id); return $object; }

	public function pick($id): ?Ghost{
		if ($id === null) return null;

		$object = $this->cache->get($id);
		if (is_null($object)){
			$record = $this->dbRepository->pick($id);
			if ($record){
				$object = $this->newGhost()->compose($record);
				$this->addToCache($object);
			}else $object = null;
		}
		return $object;
	}

	public function collect(array $ids): array{
		$objects = [];
		$ids = array_unique($ids);
		$requested = count($ids);
		if ($requested == 0) return [];

		foreach ($ids as $index => $id){
			$cached = $this->cache->get($id);
			if (!is_null($cached)){
				$objects[] = $cached;
				unset($ids[$index]);
			}
		}
		if (count($ids)){
			$records = $this->dbRepository->collect($ids);
			foreach ($records as $record){
				$object = $this->newGhost()->compose($record);
				$this->addToCache($object);
				$objects[] = $object;
			}
		}
		return $objects;
	}

	protected function newGhost():Ghost{ return new $this->ghost(); }

	protected function count(Filter $filter = null){ return $this->dbRepository->count($filter); }

	public function insert(Ghost $object){
		$record = $object->decompose();
		return $this->dbRepository->insert($record);
	}

	public function update(Ghost $object){
		$record = $object->decompose();
		return $this->dbRepository->update($record);
	}

	public function delete(int $id){
		$this->cache->delete($id);
		return $this->dbRepository->delete($id);
	}

	public function search(Filter $filter = null): AbstractFinder{
		$finder = $this->dbRepository->search($filter);
		$finder->setConverter(function ($record){
			$object = $this->newGhost()->compose($record);
			return $this->addToCache($object);
		});
		return $finder;
	}
}