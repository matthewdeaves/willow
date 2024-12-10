<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Slugs Controller
 *
 * @property \App\Model\Table\SlugsTable $Slugs
 */
class SlugsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Slugs->find()
            ->select([
                'Slugs.id',
                'Slugs.model',
                'Slugs.foreign_key',
                'Slugs.slug',
                'Slugs.created',
            ]);

        
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Slugs.slug LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $slugs = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('slugs', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('slugs'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $slug = $this->Slugs->get($id, contain: ['Articles']);
        $this->set(compact('slug'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
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
        $articles = $this->Slugs->Articles->find('list', limit: 200)->all();
        $this->set(compact('slug', 'articles'));

        return null;
    }

    public function edit($id = null)
    {
        $slug = $this->Slugs->find()
            ->where(['id' => $id])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $slug = $this->Slugs->patchEntity($slug, $this->request->getData());
            if ($this->Slugs->save($slug)) {
                $this->Flash->success(__('The slug has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The slug could not be saved. Please, try again.'));
        }

        // Get related records based on the model type
        $relatedRecords = $this->fetchTable($slug->model)->find('list', limit: 200)->all();
        $this->set(compact('relatedRecords'));

        $this->set(compact('slug'));
        return null;
    }
    
    /**
     * Delete method
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $slug = $this->Slugs->get($id);
        if ($this->Slugs->delete($slug)) {
            $this->Flash->success(__('The slug has been deleted.'));
        } else {
            $this->Flash->error(__('The slug could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
