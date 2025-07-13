<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * CookieConsents Controller
 *
 * @property \App\Model\Table\CookieConsentsTable $CookieConsents
 */
class CookieConsentsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->CookieConsents->find()
            ->contain(['Users']);
        $cookieConsents = $this->paginate($query);

        $this->set(compact('cookieConsents'));
    }

    /**
     * View method
     *
     * @param string|null $id Cookie Consent id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $cookieConsent = $this->CookieConsents->get($id, contain: ['Users']);
        $this->set(compact('cookieConsent'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $cookieConsent = $this->CookieConsents->newEmptyEntity();
        if ($this->request->is('post')) {
            $cookieConsent = $this->CookieConsents->patchEntity($cookieConsent, $this->request->getData());
            if ($this->CookieConsents->save($cookieConsent)) {
                $this->Flash->success(__('The cookie consent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The cookie consent could not be saved. Please, try again.'));
        }
        $users = $this->CookieConsents->Users->find('list', limit: 200)->all();
        $this->set(compact('cookieConsent', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Cookie Consent id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $cookieConsent = $this->CookieConsents->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $cookieConsent = $this->CookieConsents->patchEntity($cookieConsent, $this->request->getData());
            if ($this->CookieConsents->save($cookieConsent)) {
                $this->Flash->success(__('The cookie consent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The cookie consent could not be saved. Please, try again.'));
        }
        $users = $this->CookieConsents->Users->find('list', limit: 200)->all();
        $this->set(compact('cookieConsent', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Cookie Consent id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $cookieConsent = $this->CookieConsents->get($id);
        if ($this->CookieConsents->delete($cookieConsent)) {
            $this->Flash->success(__('The cookie consent has been deleted.'));
        } else {
            $this->Flash->error(__('The cookie consent could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
