<?php namespace Eternity2\Module\Codex\Action;

use Eternity2\Module\Codex\Codex\AdminRegistry;
use Eternity2\Mission\Web\Responder\JsonResponder;
use Eternity2\Zuul\AuthServiceInterface;
use Symfony\Component\HttpFoundation\Response;
abstract class Responder extends JsonResponder{

	/** @var \Eternity2\Module\Codex\Codex\AdminDescriptor */
	protected $adminDescriptor;
	/** @var \Eternity2\Zuul\AuthServiceInterface */
	protected $authService;
	/** @var \Eternity2\Module\Codex\Codex\AdminRegistry */
	private $adminRegistry;

	public function __construct(AdminRegistry $adminRegistry, AuthServiceInterface $authService){
		$this->authService = $authService;
		$this->adminRegistry = $adminRegistry;
	}

	protected function respond(){
		$this->adminDescriptor = $this->adminRegistry->get($this->getPathBag()->get('form'));
		if (!$this->authService->checkPermission($this->adminDescriptor->getPermission($this->getRequiredPermissionType()))){
			$this->getResponse()->setStatusCode(Response::HTTP_FORBIDDEN);
			return false;
		}else return $this->codexRespond();
	}

	abstract protected function getRequiredPermissionType(): ?string;

	abstract protected function codexRespond(): ?array;
}