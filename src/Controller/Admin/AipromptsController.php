<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Aiprompts Controller
 *
 * @property \App\Model\Table\AipromptsTable $Aiprompts
 */
class AipromptsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $query = $this->Aiprompts->find()
            ->select([
                'Aiprompts.id',
                'Aiprompts.task_type',
                'Aiprompts.system_prompt',
                'Aiprompts.model',
                'Aiprompts.max_tokens',
                'Aiprompts.temperature',
                'Aiprompts.created_at',
                'Aiprompts.modified_at',
            ]);

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'Aiprompts.task_type LIKE' => '%' . $search . '%',
                        'Aiprompts.system_prompt LIKE' => '%' . $search . '%',
                        'Aiprompts.model LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $aiprompts = $query->all();
            $this->set(compact('aiprompts', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $this->paginate = [
            'sortableFields' => [
        'task_type',
        'system_prompt',
        'model',
            ],
            'order' => ['Articles.created' => 'DESC'],
        ];

        $aiprompts = $this->paginate($query);
        $this->set(compact('aiprompts'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Aiprompt id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $aiprompt = $this->Aiprompts->get($id, contain: []);
        $this->set(compact('aiprompt'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $aiprompt = $this->Aiprompts->newEmptyEntity();
        if ($this->request->is('post')) {
            $aiprompt = $this->Aiprompts->patchEntity($aiprompt, $this->request->getData());
            if ($this->Aiprompts->save($aiprompt)) {
                $this->Flash->success(__('The aiprompt has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The aiprompt could not be saved. Please, try again.'));
        }
        $this->set(compact('aiprompt'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Aiprompt id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $aiprompt = $this->Aiprompts->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $aiprompt = $this->Aiprompts->patchEntity($aiprompt, $this->request->getData());
            if ($this->Aiprompts->save($aiprompt)) {
                $this->Flash->success(__('The aiprompt has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The aiprompt could not be saved. Please, try again.'));
        }
        $this->set(compact('aiprompt'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Aiprompt id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $aiprompt = $this->Aiprompts->get($id);
        if ($this->Aiprompts->delete($aiprompt)) {
            $this->Flash->success(__('The aiprompt has been deleted.'));
        } else {
            $this->Flash->error(__('The aiprompt could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
