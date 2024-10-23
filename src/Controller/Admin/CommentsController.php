<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Comments Controller
 *
 * @property \App\Model\Table\CommentsTable $Comments
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class CommentsController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = $this->fetchTable('Articles');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $query = $this->Comments->find()
            ->contain(['Users'])
            ->orderBy(['Comments.created' => 'DESC']);

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'content LIKE' => '%' . $search . '%',
                ]);
            }
            $comments = $query->all();
            $this->set(compact('comments'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $comments = $this->paginate($query);
        $this->set(compact('comments'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $comment = $this->Comments->get($id, contain: ['Users']);
        $this->set(compact('comment'));
    }

    /**
     * Edit method
     *
     * Updates a comment and clears the cache for the associated article.
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $comment = $this->Comments->get($id, contain: ['Articles']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                // Clear the cache for the associated article
                if ($comment->article) {
                    $this->Articles->clearFromCache($comment->article->slug);
                }
                $this->Flash->success(__('The comment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        $users = $this->Comments->Users->find('list', limit: 200)->all();
        $this->set(compact('comment', 'users'));

        return null;
    }

    /**
     * Delete method
     *
     * Deletes a comment and clears the cache for the associated article.
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $comment = $this->Comments->get($id);
        if ($this->Comments->delete($comment)) {
            // Clear the cache for the associated article
            if ($comment->article) {
                $this->Articles->clearFromCache($comment->article->slug);
            }

            $this->Flash->success(__('The comment has been deleted.'));
        } else {
            $this->Flash->error(__('The comment could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
