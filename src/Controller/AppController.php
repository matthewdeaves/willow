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

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Log\LogTrait;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
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
     * This method is executed before each controller action. It checks if the current
     * request is for an admin-prefixed route. If so, it verifies whether the
     * authenticated user has admin privileges by checking the 'is_admin' flag in the Users table.
     * If the user is not an admin, it logs the unauthorized access attempt and redirects the user
     * to the login page with an error message. ToDo consider moving admin acces check to AdminPlugin
     * and refactoring this code
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return \Cake\Http\Response|null Redirects to the login page if the user is not an admin.
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        if ($this->request->getParam('prefix') === 'Admin') {
            // Force a fresh check of the is_admin flag
            $identity = $this->Authentication->getIdentity();
            if ($identity) {
                $usersTable = $this->fetchTable('Users');
                $user = $usersTable->find()
                    ->select(['is_admin'])
                    ->where(['id' => $identity->getIdentifier()])
                    ->first()
                    ->is_admin;
                if (!$user) {
                    // Log the unauthorized access attempt
                    $this->log(
                        'Unauthorized access attempt to admin area',
                        'warning',
                        [
                            'group_name' => 'unauthorized_admin_access_attempt',
                            'user_id' => $this->request->getAttribute('identity')->getIdentifier(),
                            'url' => $this->request->getRequestTarget(),
                            'ip' => $this->request->clientIp(),
                            'scope' => ['system'],
                        ]
                    );

                    $this->Flash->error('Access denied. You must be an admin to view this page.');

                    return $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
                }
            }
        }

        return null;
    }

    /**
     * Executes before the view is rendered.
     *
     * This method sets the appropriate theme based on whether the current request
     * is for an admin route or not. It uses the 'prefix' parameter to determine
     * if it's an admin route and sets the theme accordingly using configuration values.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered.
     * @return void
     * @uses \Cake\Http\ServerRequest::getParam()
     * @uses \Cake\View\ViewBuilder::setTheme()
     * @uses \Cake\Core\Configure::read()
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        // Check if the current request is for an admin route or not and set the theme
        if ($this->request->getParam('prefix') === 'Admin') {
            $this->viewBuilder()->setTheme(Configure::read('Theme.admin_theme', 'AdminTheme'));
        } else {
            $this->viewBuilder()->setTheme(Configure::read('Theme.default_theme', 'DefaultTheme'));
        }
    }
}
