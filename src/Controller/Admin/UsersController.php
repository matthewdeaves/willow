<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
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
        $this->Authentication->allowUnauthenticated(['login']);

        return null;
    }

    /**
     * Index method for Users.
     *
     * This method handles both standard and AJAX requests for listing users.
     * For standard requests, it paginates the user data.
     * For AJAX requests, it performs a search based on the 'search' query parameter.
     *
     * @return \Cake\Http\Response The response object containing the rendered view.
     * @uses \Cake\ORM\Table::find() To create a query object for retrieving user data.
     * @uses \Cake\ORM\Query::select() To specify the fields to be selected from the Users table.
     * @uses \Cake\Http\ServerRequest::is() To check if the request is an AJAX request.
     * @uses \Cake\Http\ServerRequest::getQuery() To retrieve the 'search' query parameter.
     * @uses \Cake\ORM\Query::where() To apply search conditions to the query.
     * @uses \Cake\ORM\Query::all() To execute the query and retrieve all matching records.
     * @uses \Cake\Controller\Controller::set() To pass data to the view.
     * @uses \Cake\View\ViewBuilder::setLayout() To set the layout for AJAX responses.
     * @uses \Cake\Controller\Controller::render() To render the view.
     * @uses \Cake\Controller\Controller::paginate() To paginate the query results for non-AJAX requests.
     */
    public function index(): Response
    {
        $query = $this->Users->find()
            ->select([
                'Users.id',
                'Users.username',
                'Users.email',
                'Users.is_admin',
                'Users.is_disabled',
                'Users.created',
                'Users.modified',
                'Users.picture',
            ]);

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'Users.username LIKE' => '%' . $search . '%',
                        'Users.email LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $users = $query->all();
            $this->set(compact('users'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $users = $this->paginate($query);
        $this->set(compact('users'));

        return $this->render();
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $user = $this->Users->get($id, contain: ['Articles', 'Comments.Articles']);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): Response
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));

        return $this->render();
    }

    /**
     * Edit method for updating user information.
     *
     * This method handles the editing of a user's details. It retrieves the user entity
     * based on the provided ID and processes PATCH, POST, or PUT requests to update the user's information.
     * The method implements a security check to prevent users from locking their own account
     * by changing admin status or disabling it. It uses patchEntity to apply changes and
     * attempts to save the updated user entity.
     *
     * @param string|null $id The ID of the user to edit.
     * @return \Cake\Http\Response|null Redirects to index on successful save, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): Response
    {
        $user = $this->Users->get($id, contain: []);
        $currentUser = $this->Authentication->getIdentity();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user->setAccess('is_admin', true);
            $user->setAccess('is_disabled', true);

            $data = $this->request->getData();

            // Prevent changing own admin status
            if ($user->lockAdminAccountError($currentUser->id, $data)) {
                $this->Flash->error(__('You cannot remove your own admin status.'));

                return $this->redirect(['action' => 'edit', $id]);
            }
            //prevent disabling own account
            if ($user->lockEnabledAccountError($currentUser->id, $data)) {
                $this->Flash->error(__('You cannot disable your own account.'));

                return $this->redirect(['action' => 'edit', $id]);
            }

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));

        return $this->render();
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        $currentUser = $this->Authentication->getIdentity();
        if ($currentUser->id == $user->id) {
            $this->Flash->error(__('No deleting your own account.'));

            return $this->redirect(['action' => 'index']);
        }
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
