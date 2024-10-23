<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Http\Response;

/**
 * BlockedIps Controller
 *
 * Manages CRUD operations for blocked IP addresses.
 *
 * @property \App\Model\Table\BlockedIpsTable $BlockedIps
 */
class BlockedIpsController extends AppController
{
    /**
     * Displays a paginated list of blocked IP addresses.
     *
     * @return void
     */
    public function index(): void
    {
        $query = $this->BlockedIps->find();
        $blockedIps = $this->paginate($query);

        $this->set(compact('blockedIps'));
    }

    /**
     * Displays details of a specific blocked IP address.
     *
     * @param string|null $id Blocked IP id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $blockedIp = $this->BlockedIps->get($id, contain: []);
        $this->set(compact('blockedIp'));
    }

    /**
     * Adds a new blocked IP address.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $blockedIp = $this->BlockedIps->newEmptyEntity();
        if ($this->request->is('post')) {
            $blockedIp = $this->BlockedIps->patchEntity($blockedIp, $this->request->getData());
            if ($this->BlockedIps->save($blockedIp)) {
                Cache::clear('ip_blocker');
                $this->Flash->success(__('The blocked ip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The blocked ip could not be saved. Please, try again.'));
        }
        $this->set(compact('blockedIp'));

        return null;
    }

    /**
     * Edits an existing blocked IP address.
     *
     * @param string|null $id Blocked IP id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $blockedIp = $this->BlockedIps->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $blockedIp = $this->BlockedIps->patchEntity($blockedIp, $this->request->getData());
            if ($this->BlockedIps->save($blockedIp)) {
                Cache::clear('ip_blocker');
                $this->Flash->success(__('The blocked ip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The blocked ip could not be saved. Please, try again.'));
        }
        $this->set(compact('blockedIp'));

        return null;
    }

    /**
     * Deletes a blocked IP address.
     *
     * @param string|null $id Blocked IP id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $blockedIp = $this->BlockedIps->get($id);
        if ($this->BlockedIps->delete($blockedIp)) {
            Cache::clear('ip_blocker');
            $this->Flash->success(__('The blocked ip has been deleted.'));
        } else {
            $this->Flash->error(__('The blocked ip could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
