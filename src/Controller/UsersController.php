<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * beforeFilter method
     *
     * @param \Cake\Event\EventInterface $event The event object that contains the request and response objects.
     * @return void
     * @throws \Cake\Http\Exception\RedirectException If a redirect is necessary.
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['login', 'logout', 'register']);

        return null;
    }

    /**
     * Handles user login functionality.
     *
     * This method checks the authentication result to determine if a user is logged in.
     * If the user is authenticated and has admin privileges, they are redirected to the admin articles page.
     * Otherwise, the user is redirected to the page they were attempting to access before logging in,
     * or to the home page if no redirect target is set.
     * If the login attempt fails, an error message is displayed.
     *
     * @return \Cake\Http\Response|null Redirects on successful login, or returns null on failure.
     */
    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();

        // If the user is logged in send them away.
        if ($result != null && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            if ($user->is_admin) {
                // Redirect to the admin articles page
                return $this->redirect('/admin/articles');
            }

            // Redirect to the page the user was trying to access before logging in
            $target = $this->Authentication->getLoginRedirect() ?? '/';

            return $this->redirect($target);
        }

        // Handle login failure
        if ($this->request->is('post')) {
            $this->Flash->error('Invalid username or password');
        }

        return null;
    }

    /**
     * logout action to logout a user
     *
     * @return \Cake\Http\Response|null Redirects to the login page.
     */
    public function logout(): ?Response
    {
        $this->Authentication->logout();

        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Handles user registration.
     *
     * This method creates a new user entity and processes the registration form submission.
     * It ensures that the username is set to the user's email and that the is_admin flag is
     * set to false for new registrations. If the registration is successful, a success message
     * is displayed and the user is redirected to the login page. If the registration fails,
     * an error message is displayed.
     *
     * @return \Cake\Http\Response|null Redirects to login page on successful registration, or renders view otherwise.
     */
    public function register(): ?Response
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            // Set username to be the same as email
            $data['username'] = $data['email'];

            $user = $this->Users->patchEntity($user, $data);
            // Be super certain is_admin is false for new registrations
            $user->is_admin = false;
            $user->setAccess('is_admin', false);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Registration successful. Please log in.'));

                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Registration failed. Please, try again.'));
        }
        $this->set(compact('user'));

        return $this->render();
    }

    /**
     * Edit the current user's account information.
     *
     * This method allows a user to edit their own account details. It implements
     * security checks to prevent unauthorized access to other users' accounts.
     * If an unauthorized edit attempt is detected, it logs the incident and
     * redirects the user to their own edit page.
     *
     * The method handles PATCH, POST, and PUT requests to update user information.
     * It ensures that users cannot change their admin status during the update.
     * Upon successful update, a success message is displayed and the user is
     * redirected to their edit page. If the update fails, an error message is shown.
     *
     * @param string|null $id The ID of the user to be edited. Defaults to null.
     * @return \Cake\Http\Response|null Redirects to the edit page of the current user
     *                                  if unauthorized access is attempted or after
     *                                  a successful update.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When the user record is not found.
     */
    public function edit(?string $id = null): Response
    {
        // Get the currently logged-in user's ID
        $currentUserId = $this->Authentication->getIdentity()->getIdentifier();

        // Check if the ID to be edited matches the current user's ID
        if (intval($id) !== intval($currentUserId)) {
            $this->log('Unauthorized access attempt to edit another user\'s account', 'warning', [
                'group_name' => 'unauthorized_user_edit_attempt',
                'user_id' => $currentUserId,
                'attempted_user_id' => $id,
                'url' => $this->request->getRequestTarget(),
                'ip' => $this->request->clientIp(),
                'scope' => ['user'],
            ]);
            $this->Flash->error(__('You are not authorized to edit this account, stick to your own.'));

            return $this->redirect(['action' => 'edit', $this->Authentication->getIdentity()->getIdentifier()]);
        }

        $user = $this->Users->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            // Ensure the user can't change their admin status
            $user->is_admin = 0;
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your account has been updated.'));

                return $this->redirect(['action' => 'edit', $currentUserId]);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));

        return $this->render();
    }
}
