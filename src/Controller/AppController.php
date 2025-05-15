<?php
declare(strict_types=1);

namespace App\Controller;

use App\Utility\I18nManager;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
// No explicit `use Cake\Http\Response;` needed just for redirecting in beforeFilter

class AppController extends Controller
{
    use LogTrait;

    /**
     * Store a cache key available for all controllers to use
     * Created in the initialize method
     *
     * @var string
     */
    public string $cacheKey;

    /**
     * Checks if the current request is an admin request.
     *
     * This method determines whether the request is intended for the admin section
     * of the application by checking if the 'prefix' routing parameter is set to 'Admin'.
     *
     * @return bool Returns true if the request is for the admin section, false otherwise.
     */
    private function isAdminRequest(): bool
    {
        return $this->request->getParam('prefix') === 'Admin';
    }

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->cacheKey = hash('xxh3', json_encode($this->request->getAttribute('params')));
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication'); // Loads the component
        $this->loadComponent('DefaultTheme.FrontEndSite');
    }

    /**
     * beforeFilter callback.
     *
     * Executed before each controller action. Checks for admin access rights
     * when accessing admin-prefixed routes.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event); // Call parent's beforeFilter

        I18nManager::setLocaleForLanguage($this->request->getParam('lang', 'en'));

        $identity = null;
        // The AuthenticationComponent (loaded in initialize) makes the identity available.
        // It relies on the AuthenticationMiddleware having run first to populate the request attribute.
        if ($this->components()->has('Authentication')) {
            $identity = $this->Authentication->getIdentity();
        }

        if ($identity) {
            $profilePicValue = $identity->get('image');
            if ($profilePicValue) {
                $userEntity = $this->fetchTable('Users')->newEntity(
                    ['image' => $profilePicValue, 'dir' => $identity->get('dir')],
                );
                $profilePic = $userEntity->get('image_url');
                $this->set(compact('profilePic'));
            }
        }

        if ($this->isAdminRequest()) {
            // If there is no identity, or the identifier part of the identity is null/empty
            if (!$identity || !$identity->getIdentifier() || $identity->get('is_admin') == false) {
                $this->Flash->error(__('Access denied. You must be logged in as an admin to view this page.'));
                $event->setResult($this->redirect(['_name' => 'home', 'prefix' => false]));

                return;
            }

            I18nManager::setLocalForAdminArea();
        }

        $this->handleConsent();

        $this->set('activeCtl', $this->request->getParam('controller'));
        $this->set('activeAct', $this->request->getParam('action'));
    }

    /**
     * Handles user consent data processing and view variable setting.
     *
     * This method:
     * - Starts a session if not already started
     * - For non-admin requests:
     *   - Gets the user ID if authenticated
     *   - Gets the current session ID
     *   - Retrieves and decodes the consent cookie if present
     * - Sets session ID and consent data for view access
     *
     * @return void The method sets view variables but does not return a value
     * @throws \RuntimeException If session cannot be started
     * @throws \InvalidArgumentException If cookie data cannot be decoded
     */
    private function handleConsent(): void
    {
        if (!$this->request->getSession()->started()) { // Ensure session is started
            $this->request->getSession()->start();
        }
        $sessionId = $this->request->getSession()->id();
        $consentData = null;
        $consentCookie = $this->request->getCookie('consent_cookie');
        if ($consentCookie) {
            $consentData = json_decode($consentCookie, true);
        }
        $this->set(compact('sessionId', 'consentData'));
    }

    /**
     * beforeRender method
     *
     * This method is called before the controller action is rendered. It
     * sets the theme for the view based on the prefix of the request.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $theme = $this->request->getParam('prefix') === 'Admin'
            ? Configure::read('Theme.admin_theme', 'AdminTheme')
            : Configure::read('Theme.default_theme', 'DefaultTheme');

        $this->viewBuilder()->setTheme($theme);
    }
}
