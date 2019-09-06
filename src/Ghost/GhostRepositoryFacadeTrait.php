<?php namespace Eternity2\Ghost;

use Eternity2\DBAccess\Filter\Filter;
use Eternity2\DBAccess\Finder\AbstractFinder;
/**
 * @mixin Ghost
 */
trait GhostRepositoryFacadeTrait{

	/** @return self */
	static final public function pick($id): ?self{
		return static::$model->repository->pick($id);
	}

	/** @return self[] */
	static final public function collect($ids): array{
		return static::$model->repository->collect($ids);
	}

	static final public function search(Filter $filter = null): AbstractFinder{
		return static::$model->repository->search($filter);
	}
}