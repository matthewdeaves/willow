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

use Cake\Core\Configure;
use Cake\Controller\Controller;

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

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * beforeFilter callback.
     *
     * This method is executed before each controller action. It checks if the current request is for an admin-prefixed route.
     * If so, it verifies whether the authenticated user has admin privileges by checking the 'is_admin' flag in the Users table.
     * If the user is not an admin, it logs the unauthorized access attempt and redirects the user to the login page with an error message.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return \Cake\Http\Response|null Redirects to the login page if the user is not an admin.
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        if ($this->request->getParam('prefix') === 'Admin') {
            // Force a fresh check of the is_admin flag
            $identity = $this->Authentication->getIdentity();
            if($identity) {
                $usersTable = $this->fetchTable('Users');
                $user = $usersTable->find()
                    ->select(['is_admin'])
                    ->where(['id' => $identity->getIdentifier()])
                    ->first()
                    ->is_admin; //todo: test this with logged in/out under 2 sites multi ten
                if (!$user) {
                    // Log the unauthorized access attempt
                    $this->log('Unauthorized access attempt to admin area', 'warning', [
                        '$identity = $this->Authentication->getIdentity();group_name' => 'unauthorized_admin_access_attempt',
                        'user_id' => $this->request->getAttribute('identity')->getIdentifier(),
                        'url' => $this->request->getRequestTarget(),
                        'ip' => $this->request->clientIp(),
                        'scope' => ['system']
                    ]);

                    $this->Flash->error('Access denied. You must be an admin to view this page.');
                    return $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
                }
            }
        }
    }

    /**
     * beforeRender callback.
     *
     * This method is called after the controller's beforeRender method but before the controller renders
     * the view and layout. It sets view variables and determines the layout based on the request parameters.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered.
     * @return void
     *
     * @uses \Authentication\AuthenticationServiceInterface::getResult()
     * @uses \Authentication\AuthenticationServiceInterface::getIdentity()
     * @uses \Cake\Http\ServerRequest::getParam()
     * @uses \Cake\View\ViewBuilder::setLayout()
     *
     * - Sets the 'isLoggedIn' view variable to indicate if the user is authenticated.
     * - If the user is authenticated, sets the 'currentUserID' view variable with the user's ID.
     * - Checks if the current request is for an admin route and sets the layout to 'admin' if true,
     *   otherwise sets the layout to 'default'.
     */
    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        parent::beforeRender($event);
        // Set the 'isLoggedIn' view variable to indicate if the user is authenticated
        $this->set('isLoggedIn', $this->Authentication->getResult()->isValid());
        if ($this->Authentication->getIdentity()) {
            $this->set('currentUserID', $this->Authentication->getIdentity()->id);
        }

        // Check if the current request is for an admin route
        if ($this->request->getParam('prefix') === 'Admin') {
            $this->viewBuilder()->setLayout('admin');
        } else {
            $this->viewBuilder()->setLayout('default');
        }
    }
}
