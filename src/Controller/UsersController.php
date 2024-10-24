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
 * Handles user-related operations such as registration, login, logout, and account management.
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use LogTrait;

    /**
     * Configures actions that can be accessed without authentication.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return \Cake\Http\Response|null
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
     * Authenticates the user and redirects them based on their role and previous page.
     *
     * @return \Cake\Http\Response|null Redirects on successful login, or null on failure.
     */
    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();
        if ($result != null && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            if ($user->is_admin) {
                return $this->redirect('/admin/articles');
            }

            $target = $this->Authentication->getLoginRedirect() ?? '/';

            return $this->redirect($target);
        }

        if ($this->request->is('post')) {
            $this->Flash->error(__('Invalid username or password'));
        }

        return null;
    }

    /**
     * Logs out the current user.
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
     * Creates a new user account and sends a confirmation email.
     *
     * @return \Cake\Http\Response|null Redirects on successful registration, or null on failure.
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
                        __('Registration successful, but there was an issue creating the confirmation link.')
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
     * Sends a confirmation email to the user.
     *
     * @param \App\Model\Entity\User $user The user entity.
     * @param \App\Model\Entity\UserAccountConfirmation $confirmation The confirmation entity.
     * @return void
     */
    private function sendConfirmationEmailMessage(User $user, UserAccountConfirmation $confirmation): void
    {
        if (env('CAKE_ENV') === 'test') {
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
     * Allows a user to edit their own account information.
     *
     * @param string|null $id The ID of the user to be edited.
     * @return \Cake\Http\Response|null Redirects after editing, or null on GET requests.
     */
    public function edit(?string $id = null): ?Response
    {
        $currentUserId = $this->Authentication->getIdentity()->getIdentifier();

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
     * @param string $confirmationCode The confirmation code to validate.
     * @return \Cake\Http\Response|null Redirects after confirmation attempt.
     */
    public function confirmEmail(string $confirmationCode): ?Response
    {
        $confirmationsTable = $this->fetchTable('UserAccountConfirmations');
        $confirmation = $confirmationsTable->find()
            ->where(['confirmation_code' => $confirmationCode])
            ->first();

        if ($confirmation) {
            $user = $this->Users->get($confirmation->user_id);
            $user->setAccess('is_disabled', true);
            $user->is_disabled = false;

            if ($this->Users->save($user)) {
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
