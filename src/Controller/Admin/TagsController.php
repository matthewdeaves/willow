<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Tags Controller
 *
 * Manages tags, providing functionalities to list, view, add, edit, and delete tags.
 *
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    /**
     * Index method for Tags Controller
     *
     * Handles the display of tags. Supports both standard and AJAX requests.
     * For AJAX requests, performs a search based on the 'search' query parameter and returns
     * the results in an 'ajax' layout. For standard requests, paginates the tags.
     *
     * @return \Cake\Http\Response|null Returns a Response object for AJAX requests, null otherwise.
     */
    public function index(): ?Response
    {
        $query = $this->Tags->find()
            ->select([
                'Tags.id',
                'Tags.title',
                'Tags.slug',
                'Tags.image',
                'Tags.alt_text',
                'Tags.created',
                'Tags.modified',
                'Tags.meta_title',
                'Tags.meta_description',
                'Tags.meta_keywords',
            ]);

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'Tags.title LIKE' => '%' . $search . '%',
                        'Tags.slug LIKE' => '%' . $search . '%',
                        'Tags.meta_title LIKE' => '%' . $search . '%',
                        'Tags.meta_description LIKE' => '%' . $search . '%',
                        'Tags.meta_keywords LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $tags = $query->all();
            $this->set(compact('tags', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $tags = $this->paginate($query);
        $this->set(compact('tags'));

        return null;
    }

    /**
     * View method
     *
     * Displays details of a specific tag, including associated articles and their authors.
     *
     * @param string|null $id Tag id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $tag = $this->Tags->get($id, contain: ['Articles.Users']);
        $this->set(compact('tag'));
    }

    /**
     * Add method
     *
     * Handles the creation of a new tag.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, null otherwise.
     */
    public function add(): ?Response
    {
        $tag = $this->Tags->newEmptyEntity();
        if ($this->request->is('post')) {
            $tag = $this->Tags->patchEntity($tag, $this->request->getData());
            if ($this->Tags->save($tag)) {
                $this->Flash->success(__('The tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The tag could not be saved. Please, try again.'));
        }
        $articles = $this->Tags->Articles->find('list', limit: 200)->all();
        $this->set(compact('tag', 'articles'));

        return null;
    }

    /**
     * Edit method
     *
     * Handles the editing of an existing tag.
     *
     * @param string|null $id Tag id.
     * @return \Cake\Http\Response|null Redirects on successful edit, null otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $tag = $this->Tags->get($id, contain: ['Articles']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $tag = $this->Tags->patchEntity($tag, $this->request->getData());
            if ($this->Tags->save($tag)) {
                $this->Flash->success(__('The tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The tag could not be saved. Please, try again.'));
        }
        $articles = $this->Tags->Articles->find('list', limit: 200)->all();
        $this->set(compact('tag', 'articles'));

        return null;
    }

    /**
     * Delete method
     *
     * Handles the deletion of a tag.
     *
     * @param string|null $id Tag id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Http\Exception\MethodNotAllowedException When invalid method is used.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $tag = $this->Tags->get($id);
        if ($this->Tags->delete($tag)) {
            $this->Flash->success(__('The tag has been deleted.'));
        } else {
            $this->Flash->error(__('The tag could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
