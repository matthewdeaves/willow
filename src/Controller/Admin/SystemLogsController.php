<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * SystemLogs Controller
 *
 * @property \App\Model\Table\SystemLogsTable $SystemLogs
 */
class SystemLogsController extends AppController
{
    /**
     * Index method for SystemLogs.
     *
     * This method retrieves and displays a list of system logs. It supports filtering by log level and group name,
     * and allows for searching within the logs when accessed via AJAX. The logs are ordered by creation date in
     * descending order. The method also retrieves distinct log levels and group names for filtering options.
     *
     * @return \Cake\Http\Response The response object containing the rendered view.
     * @throws \Cake\Http\Exception\NotFoundException If the page is not found.
     * @throws \Cake\Database\Exception\DatabaseException If there's an issue with the database query.
     * @uses \App\Model\Table\SystemLogsTable::find()
     * @uses \Cake\Http\ServerRequest::getQuery()
     * @uses \Cake\Http\ServerRequest::is()
     * @uses \Cake\View\ViewBuilder::setLayout()
     * @uses \Cake\Controller\Controller::paginate()
     * @uses \Cake\Controller\Controller::set()
     * @uses \Cake\Controller\Controller::render()
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
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $systemLog = $this->SystemLogs->get($id, contain: []);
        $this->set(compact('systemLog'));
    }

    /**
     * Delete method
     *
     * @param string|null $id System Log id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
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
