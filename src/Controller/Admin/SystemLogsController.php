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
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): void
    {
        $systemLogs = $this->SystemLogs->find()
            ->orderBy(['group_name' => 'ASC', 'created' => 'DESC'])
            ->all()
            ->groupBy('group_name')
            ->toArray();

        $this->set(compact('systemLogs'));
    }

    /**
     * View method
     *
     * @param string|null $id System Log id.
     * @return \Cake\Http\Response|null|void Renders view
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
    public function delete(?string $id = null): Response
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

    /**
     * Deletes all system logs, optionally filtered by group name.
     *
     * This method allows only POST and DELETE HTTP methods to prevent accidental deletions via GET requests.
     * It attempts to delete all records in the SystemLogs table, or only those matching the specified group name.
     * If successful, a success message is displayed to the user. If the deletion fails, an error message is shown.
     *
     * @param string|null $group_name Optional. The group name to filter logs for deletion.
     * @return \Cake\Http\Response|null Redirects to the index action after attempting to delete system logs.
     * @throws \Cake\Http\Exception\MethodNotAllowedException If the request method is not POST or DELETE.
     */
    public function deleteAll(?string $group_name = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $conditions = [];
        if ($group_name !== null) {
            $conditions['group_name'] = $group_name;
        }

        if ($this->SystemLogs->deleteAll($conditions)) {
            if ($group_name) {
                $this->Flash->success(__('All system logs for group "{0}" have been deleted.', $group_name));
            } else {
                $this->Flash->success(__('All system logs have been deleted.'));
            }
        } else {
            if ($group_name) {
                $this->Flash->error(__('Unable to delete system logs for group "{0}". Please try again.', $group_name));
            } else {
                $this->Flash->error(__('Unable to delete all system logs. Please try again.'));
            }
        }

        return $this->redirect(['action' => 'index']);
    }
}
