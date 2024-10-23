<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Slugs Controller
 *
 * Handles CRUD operations for slugs and their associated articles.
 *
 * @property \App\Model\Table\SlugsTable $Slugs
 */
class SlugsController extends AppController
{
    /**
     * Index method for retrieving and displaying slugs and associated articles.
     *
     * Handles both standard and AJAX requests. For AJAX requests, it supports
     * searching through slugs and articles based on a search query. The results are rendered
     * using an AJAX-specific layout. For standard requests, it paginates the results and
     * renders them using the default layout.
     *
     * @return \Cake\Http\Response|null Returns Response for AJAX requests, null for standard requests
     */
    public function index(): ?Response
    {
        $query = $this->Slugs->find()
            ->select([
                'Slugs.id',
                'Slugs.slug',
                'Slugs.article_id',
                'Slugs.created',
                'Slugs.modified',
                'Articles.id',
                'Articles.title',
                'Articles.slug',
            ])
            ->contain(['Articles'])
            ->orderBy(['Slugs.modified' => 'DESC']);

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'Slugs.slug LIKE' => '%' . $search . '%',
                        'Articles.title LIKE' => '%' . $search . '%',
                        'Articles.body LIKE' => '%' . $search . '%',
                        'Articles.slug LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $slugs = $query->all();
            $this->set(compact('slugs'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $slugs = $this->paginate($query);
        $this->set(compact('slugs'));

        return null;
    }

    /**
     * View method
     *
     * Displays details of a specific slug and its associated article.
     *
     * @param string|null $id Slug id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $slug = $this->Slugs->get($id, contain: ['Articles']);
        $this->set(compact('slug'));
    }

    /**
     * Add method
     *
     * Handles the creation of a new slug.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, null otherwise.
     */
    public function add(): ?Response
    {
        $slug = $this->Slugs->newEmptyEntity();
        if ($this->request->is('post')) {
            $slug = $this->Slugs->patchEntity($slug, $this->request->getData());
            if ($this->Slugs->save($slug)) {
                $this->Flash->success(__('The slug has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The slug could not be saved. Please, try again.'));
        }
        $articles = $this->Slugs->Articles->find('list')->all();
        $this->set(compact('slug', 'articles'));

        return null;
    }

    /**
     * Edit method
     *
     * Handles the editing of an existing slug.
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response|null Redirects on successful edit, null otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $slug = $this->Slugs->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $slug = $this->Slugs->patchEntity($slug, $this->request->getData());
            if ($this->Slugs->save($slug)) {
                $this->Flash->success(__('The slug has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The slug could not be saved. Please, try again.'));
        }
        $articles = $this->Slugs->Articles->find('list', limit: 200)->all();
        $this->set(compact('slug', 'articles'));

        return null;
    }

    /**
     * Delete method
     *
     * Handles the deletion of a slug.
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Http\Exception\MethodNotAllowedException When invalid method is used.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $slug = $this->Slugs->get($id);
        if ($this->Slugs->delete($slug)) {
            $this->Flash->success(__('The slug has been deleted.'));
        } else {
            $this->Flash->error(__('The slug could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
