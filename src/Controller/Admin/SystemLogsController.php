<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Table\Trait\ArticleCacheTrait;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Exception;

/**
 * SystemLogs Controller
 *
 * @property \App\Model\Table\SystemLogsTable $SystemLogs
 */
class SystemLogsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): Response
    {
        $query = $this->SystemLogs->find()
            ->select([
                'SystemLogs.id',
                'SystemLogs.level',
                'SystemLogs.message',
                'SystemLogs.context',
                'SystemLogs.group_name',
                'SystemLogs.created',
            ])
            ->order(['SystemLogs.created' => 'DESC']);

        $levels = $this->SystemLogs->find()
            ->select(['level'])
            ->distinct(['level'])
            ->order(['level' => 'ASC'])
            ->all()
            ->extract('level')
            ->toArray();

        $groupNames = $this->SystemLogs->find()
            ->select(['group_name'])
            ->distinct(['group_name'])
            ->order(['group_name' => 'ASC'])
            ->all()
            ->extract('group_name')
            ->toArray();

        $selectedLevel = $this->request->getQuery('level');
        $selectedGroup = $this->request->getQuery('group');

        if ($selectedLevel) {
            $query->where(['SystemLogs.level' => $selectedLevel]);
        }

        if ($selectedGroup) {
            $query->where(['SystemLogs.group_name' => $selectedGroup]);
        }

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'SystemLogs.level LIKE' => '%' . $search . '%',
                        'SystemLogs.message LIKE' => '%' . $search . '%',
                        'SystemLogs.context LIKE' => '%' . $search . '%',
                        'SystemLogs.group_name LIKE' => '%' . $search . '%',
                        'DATE(SystemLogs.created) LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $systemLogs = $query->all();
            $this->set(compact('systemLogs'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $systemLogs = $this->paginate($query);
        $this->set(compact('systemLogs', 'levels', 'groupNames', 'selectedLevel', 'selectedGroup'));

        return $this->render();
    }

    /**
     * View method
     *
     * @param string|null $id System Log id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $systemLog = $this->SystemLogs->get($id, contain: []);
        $this->set(compact('systemLog'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $systemLog = $this->SystemLogs->newEmptyEntity();
        if ($this->request->is('post')) {
            $systemLog = $this->SystemLogs->patchEntity($systemLog, $this->request->getData());
            if ($this->SystemLogs->save($systemLog)) {
                $this->Flash->success(__('The system log has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The system log could not be saved. Please, try again.'));
        }
        $this->set(compact('systemLog'));
    }

    /**
     * Edit method
     *
     * @param string|null $id System Log id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $systemLog = $this->SystemLogs->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $systemLog = $this->SystemLogs->patchEntity($systemLog, $this->request->getData());
            if ($this->SystemLogs->save($systemLog)) {
                $this->Flash->success(__('The system log has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The system log could not be saved. Please, try again.'));
        }
        $this->set(compact('systemLog'));
    }

    /**
     * Delete method
     *
     * @param string|null $id System Log id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $systemLog = $this->SystemLogs->get($id);
        if ($this->SystemLogs->delete($systemLog)) {
            $this->Flash->success(__('The system log has been deleted.'));
        } else {
            $this->Flash->error(__('The system log could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
