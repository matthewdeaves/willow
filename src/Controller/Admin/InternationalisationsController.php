<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Internationalisations Controller
 *
 * @property \App\Model\Table\InternationalisationsTable $Internationalisations
 */
class InternationalisationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $query = $this->Internationalisations->find()
            ->select([
                'Internationalisations.id',
                'Internationalisations.locale',
                'Internationalisations.message_id',
                'Internationalisations.message_str',
                'Internationalisations.created_at',
                'Internationalisations.updated_at',
            ]);

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'Internationalisations.locale LIKE' => '%' . $search . '%',
                        'Internationalisations.message_id LIKE' => '%' . $search . '%',
                        'Internationalisations.message_str LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $internationalisations = $query->all();
            $this->set(compact('internationalisations'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $internationalisations = $this->paginate($query);
        $this->set(compact('internationalisations'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Internationalisation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $internationalisation = $this->Internationalisations->get($id, contain: []);
        $this->set(compact('internationalisation'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $internationalisation = $this->Internationalisations->newEmptyEntity();
        if ($this->request->is('post')) {
            $internationalisation = $this->Internationalisations->patchEntity($internationalisation, $this->request->getData());
            if ($this->Internationalisations->save($internationalisation)) {
                $this->Flash->success(__('The internationalisation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The internationalisation could not be saved. Please, try again.'));
        }
        $this->set(compact('internationalisation'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Internationalisation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $internationalisation = $this->Internationalisations->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $internationalisation = $this->Internationalisations->patchEntity($internationalisation, $this->request->getData());
            if ($this->Internationalisations->save($internationalisation)) {
                $this->Flash->success(__('The internationalisation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The internationalisation could not be saved. Please, try again.'));
        }
        $this->set(compact('internationalisation'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Internationalisation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $internationalisation = $this->Internationalisations->get($id);
        if ($this->Internationalisations->delete($internationalisation)) {
            $this->Flash->success(__('The internationalisation has been deleted.'));
        } else {
            $this->Flash->error(__('The internationalisation could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
