<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Entity\UserAccountConfirmation;
use App\Utility\SettingsManager;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Log\LogTrait;
use Cake\Queue\QueueManager;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Exception;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use LogTrait;

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

        $this->Authentication->allowUnauthenticated(['login', 'logout', 'register', 'confirmEmail']);

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
     * Handles user registration process.
     *
     * This method creates a new user entity and processes the registration form submission.
     * It ensures that the new user is not an admin and is initially disabled. Upon successful
     * registration, it creates a confirmation record and sends a confirmation email to the user.
     * If registration fails, it sets an error message and returns a 403 response status.
     *
     * @return \Cake\Http\Response The response object containing the rendered view or a redirect.
     * @throws \Cake\Datasource\Exception\InvalidPrimaryKeyException If the user entity couldn't be saved.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If the confirmation entity couldn't be saved.
     * @uses \Cake\Datasource\FactoryLocator::get()
     * @uses \Cake\Http\ServerRequest::getData()
     * @uses \Cake\Http\ServerRequest::is()
     * @uses \Cake\ORM\Table::newEmptyEntity()
     * @uses \Cake\ORM\Table::patchEntity()
     * @uses \Cake\ORM\Table::save()
     * @uses \Cake\Utility\Text::uuid()
     * @uses \Cake\View\Helper\FlashHelper::success()
     * @uses \Cake\View\Helper\FlashHelper::error()
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
            $user->is_disabled = true;
            $user->setAccess('is_disabled', true);

            if ($this->Users->save($user)) {
                // Add record to user_account_confirmations table
                $confirmationsTable = $this->fetchTable('UserAccountConfirmations');
                $confirmation = $confirmationsTable->newEntity([
                    'user_id' => $user->id,
                    'confirmation_code' => Text::uuid(),
                ]);

                if ($confirmationsTable->save($confirmation)) {
                    $this->Flash->success(__('Registration successful. Please check your email for confirmation.'));
                    $this->sendConfirmationEmailMessage($user, $confirmation);
                } else {
                    $this->Flash->error(
                        __('Registration successful, but there was an issue creating the confirmation record.')
                    );
                }

                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error(__('Registration failed. Please, try again.'));

                return $this->response->withStatus(403);
            }
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Sends a confirmation email message to the user.
     *
     * This method queues an email job to send a confirmation email to the user with a link
     * to confirm their email address. It handles exceptions by logging any errors encountered
     * during the email sending process. In test environments, it simulates email sending.
     *
     * @param \App\Model\Entity\User $user The user entity to whom the email is sent.
     * @param \App\Model\Entity\UserAccountConfirmation $confirmation The confirmation entity containing the confirmation code.
     * @return void
     * @throws \Exception If there is an error during the queue push operation, it logs the error message.
     * @uses \Cake\Log\Log::error()
     * @uses \Cake\Queue\QueueManager::push()
     * @uses \Cake\Routing\Router::url()
     * @uses \App\Utility\SettingsManager::read()
     */
    private function sendConfirmationEmailMessage(User $user, UserAccountConfirmation $confirmation): void
    {
        if (env('CAKE_ENV') === 'test') {
            // Simulate email sending for tests
            return;
        }

        try {
            $data = [
                'template_identifier' => 'confirm_email',
                'from' => SettingsManager::read('Email.reply_email', 'noreply@example.com'),
                'to' => $user->email,
                'viewVars' => [
                    'username' => $user->username,
                    'confirmation_code' => $confirmation->confirmation_code,
                    'confirm_email_link' => Router::url([
                        'controller' => 'Users',
                        'action' => 'confirmEmail',
                        $confirmation->confirmation_code,
                    ], true),
                ],
            ];

            QueueManager::push('App\Job\SendEmailJob', $data);
        } catch (Exception $e) {
            Log::error(__('Failed to send confirmation email message: {0}', $e->getMessage()));
        }
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
    public function edit(?string $id = null): ?Response
    {
        // Get the currently logged-in user's ID
        $currentUserId = $this->Authentication->getIdentity()->getIdentifier();

        // Check if the ID to be edited matches the current user's ID
        if ($id !== $currentUserId) {
            $this->log('Unauthorized access attempt to edit another user\'s account', 'warning', [
                'group_name' => 'unauthorized_user_edit_attempt',
                'user_id' => $currentUserId,
                'attempted_user_id' => $id,
                'url' => $this->request->getRequestTarget(),
                'ip' => $this->request->clientIp(),
                'scope' => ['user'],
            ]);
            $this->Flash->error(__('You are not authorized to edit this account, stick to your own.'));

            return $this->redirect(['action' => 'edit', $currentUserId]);
        }

        $user = $this->Users->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            // Ensure the user can't change their admin status
            $user->setAccess('is_admin', false);
            $user->setAccess('is_disabled', false);
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your account has been updated.'));

                return $this->redirect(['action' => 'edit', $user->id]);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));

            return $this->response->withStatus(403);
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Confirms a user's email address using a confirmation code.
     *
     * This method validates the provided confirmation code against the UserAccountConfirmations table.
     * If a valid confirmation is found, it enables the associated user account and deletes the confirmation record.
     *
     * @param string $confirmationCode The confirmation code to validate.
     * @return \Cake\Http\Response|null A redirect response to the login page on success, or
     *                                  to the register page on failure. Returns null if rendering is required.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When the user record is not found.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If the user entity couldn't be saved.
     */
    public function confirmEmail(string $confirmationCode): ?Response
    {
        $confirmationsTable = $this->fetchTable('UserAccountConfirmations');
        $confirmation = $confirmationsTable->find()
            ->where(['confirmation_code' => $confirmationCode])
            ->first();

        if ($confirmation) {
            $user = $this->Users->get($confirmation->user_id);

            // Explicitly allow modification of is_disabled
            $user->setAccess('is_disabled', true);
            $user->is_disabled = false; // Enable the user account

            if ($this->Users->save($user)) {
                // Delete the confirmation record
                $confirmationsTable->delete($confirmation);
                $this->Flash->success(__('Your account has been confirmed. You can now log in.'));

                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error(__('There was an issue confirming your account. Please try again.'));

                return $this->redirect(['action' => 'register']);
            }
        } else {
            $this->Flash->error(__('Invalid confirmation code. Please contact support.'));

            return $this->redirect(['action' => 'register']);
        }

        return null;
    }
}
