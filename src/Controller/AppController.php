<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Utility\I18nManager;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Log\LogTrait;

/**
 * Application Controller
 *
 * Base controller class for the application. All controllers should extend this class
 * to inherit common functionality and configurations.
 *
 * @property \Cake\Controller\Component\FlashComponent $Flash
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \DefaultTheme\Controller\Component\FrontEndSiteComponent $FrontEndSite
 */
class AppController extends Controller
{
    use LogTrait;

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

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('DefaultTheme.FrontEndSite');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * beforeFilter callback.
     *
     * Executed before each controller action. Checks for admin access rights
     * when accessing admin-prefixed routes.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return \Cake\Http\Response|null Redirects to login page if user lacks admin privileges.
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        I18nManager::setLocaleForLanguage($this->request->getParam('lang', 'en'));

        $identity = $this->Authentication->getIdentity();

        if ($identity) {
            $profilePic = $identity->picture;
            $this->set(compact('profilePic'));
        }

        if ($this->request->getParam('prefix') === 'Admin' && null != $identity) {
            I18nManager::setLocalForAdminArea();

            $usersTable = $this->fetchTable('Users');
            $user = $usersTable->find()
                ->select(['is_admin'])
                ->where(['id' => $identity->getIdentifier()])
                ->first();

            if (!$user || empty($user->is_admin)) {
                $this->log(
                    'Unauthorized access attempt to admin area',
                    'warning',
                    [
                        'group_name' => 'unauthorized_admin_access_attempt',
                        'user_id' => $identity->getIdentifier(),
                        'url' => $this->request->getRequestTarget(),
                        'ip' => $this->request->clientIp(),
                        'scope' => ['system'],
                    ]
                );

                $this->Flash->error(__('Access denied. You must be an admin to view this page.'));

                return $this->redirect(['_name' => 'login']);
            }
        }

        // Usefulf for setting active menu items
        $this->set('activeCtl', $this->request->getParam('controller'));
        $this->set('activeAct', $this->request->getParam('action'));

        return null;
    }

    /**
     * beforeRender callback.
     *
     * Sets the appropriate theme based on whether the current request
     * is for an admin route or not.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered.
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
