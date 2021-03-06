<?php namespace Eternity2\Module\Codex;

use Eternity2\Module\Codex\Action\CodexAttachmentCopy;
use Eternity2\Module\Codex\Action\CodexAttachmentDelete;
use Eternity2\Module\Codex\Action\CodexAttachmentGet;
use Eternity2\Module\Codex\Action\CodexAttachmentMove;
use Eternity2\Module\Codex\Action\CodexAttachmentUpload;
use Eternity2\Module\Codex\Action\CodexDeleteFormItem;
use Eternity2\Module\Codex\Action\CodexGetForm;
use Eternity2\Module\Codex\Action\CodexGetFormItem;
use Eternity2\Module\Codex\Action\CodexGetList;
use Eternity2\Module\Codex\Action\CodexInfo;
use Eternity2\Module\Codex\Action\CodexMenu;
use Eternity2\Module\Codex\Action\CodexSaveFormItem;
use Eternity2\Module\Codex\Codex\AdminRegistry;
use Eternity2\Module\SmartPageResponder\Twigger\Twigger;
use Eternity2\System\Event\EventManager;
use Eternity2\System\Module\ModuleInterface;
use Eternity2\Mission\Web\Application;
use Eternity2\Mission\Web\Routing\Router;
use Eternity2\System\ServiceManager\ServiceContainer;
use Eternity2\Thumbnail\ThumbnailResponder;

class Module implements ModuleInterface{

	/** @var \Eternity2\Module\Codex\Codex\AdminRegistry */
	private $adminRegistry;
	protected $env;

	public function __construct(AdminRegistry $adminRegistry){
		$this->adminRegistry = $adminRegistry;
	}

	protected $menu;
	public function getMenu(){ return $this->menu; }

	protected $admin = [
		'title'             => 'Codex2',
		'icon'              => 'fal fa-infinite',
		'login-placeholder' => 'e-mail',
	];
	public function getAdmin(): array{ return $this->admin; }

	public function getEnv(){ return $this->env; }

	public function __invoke($env){
		$this->env = $env;
		ServiceContainer::shared(CodexWhoAmIInterface::class)->service($env['services']['WhoAmI']);

		if (array_key_exists('menu', $env)) $this->menu = $env['menu'];
		if (array_key_exists('admin', $env)) $this->admin = $env['admin'];
		EventManager::listen(Application::EVENT_ROUTING_FINISHED, [$this, 'route']);
		EventManager::listen(Twigger::EVENT_TWIG_ENVIRONMENT_CREATED, function (){
			Twigger::Service()->addPath(__DIR__ . '/~twig/', 'codex');
		});
		if (array_key_exists('codex-forms', $env)) foreach ($env['codex-forms'] as $form) $this->register($form);
	}

	public function route(Router $router){
		// PAGES
		$router->get(env('thumbnail.url') . '/*', ThumbnailResponder::class)();
		$router->get("/", Page\Index::class)();

		$router->clearPipeline();
		// API AUTH
//		$router->pipe(AuthCheck::class, ["responder" => NotAuthorized::class]);
//		$router->pipe(PermissionCheck::class, ["responder" => Forbidden::class, "permission" => "admin"]);

		// API
		$router->get('/menu', CodexMenu::class)();
		$router->get('/{form}/codexinfo', CodexInfo::class)();
		$router->post('/{form}/get-list/{page}', CodexGetList::class)();
		$router->get('/{form}/get-form-item/{id}', CodexGetFormItem::class)();
		$router->get('/{form}/get-form', CodexGetForm::class)();
		$router->post('/{form}/save-item', CodexSaveFormItem::class)();
		$router->get('/{form}/delete-item/{id}', CodexDeleteFormItem::class)();
		$router->post('/{form}/attachment/upload/{id}', CodexAttachmentUpload::class)();
		$router->get('/{form}/attachment/get/{id}', CodexAttachmentGet::class)();
		$router->post('/{form}/attachment/move/{id}', CodexAttachmentMove::class)();
		$router->post('/{form}/attachment/copy/{id}', CodexAttachmentCopy::class)();
		$router->post('/{form}/attachment/delete/{id}', CodexAttachmentDelete::class)();

//		$router->get('/menu', Action\GetMenu::class)();
		$router->get('/', Page\Index::class)();
	}

	public function register($form){ $this->adminRegistry->registerForm($form); }

}


