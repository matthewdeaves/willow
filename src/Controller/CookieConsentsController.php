<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * CookieConsents Controller
 *
 * @property \App\Model\Table\CookieConsentsTable $CookieConsents
 */
class CookieConsentsController extends AppController
{
    /**
     * Configures authentication for specific actions.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['edit']);

        return null;
    }

    /**
     * Edit or create cookie consent preferences with GDPR compliance.
     *
     * This method handles both logged-in and non-logged-in users' cookie preferences.
     * It maintains a complete audit trail by creating new records rather than updating existing ones.
     * The method first attempts to find an existing consent record by the current session ID.
     * If no record is found and the user is logged in, it searches for their most recent consent record.
     * For new visitors or those without existing records, a new empty consent record is created.
     *
     * The method stores required GDPR compliance data including:
     * - Session ID for tracking anonymous users
     * - User ID for authenticated users
     * - IP address of the request
     * - User Agent string
     *
     * @return \Cake\Http\Response|null Redirects to edit action on successful save, null otherwise
     * @throws \Cake\Http\Exception\NotFoundException When invalid parameters are passed
     * @throws \Cake\Database\Exception\DatabaseException When database operations fail
     */
    public function edit(): ?Response
    {
        $sessionId = $this->request->getSession()->id();
        $userId = $this->request->getAttribute('identity') ?
            $this->request->getAttribute('identity')->getIdentifier() : null;

        // Try to find the record by session ID first
        $cookieConsent = $this->CookieConsents->find()
            ->where(['session_id' => $sessionId])
            ->order(['created' => 'DESC'])
            ->first();

        // If not found by session ID and user is logged in, try by user ID
        if (!$cookieConsent && $userId) {
            $cookieConsent = $this->CookieConsents->find()
                ->where(['user_id' => $userId])
                ->order(['created' => 'DESC'])
                ->first();
        }

        // If no record exists, create a new one
        if (!$cookieConsent) {
            $cookieConsent = $this->CookieConsents->newEmptyEntity();
            $cookieConsent->session_id = $sessionId;
            $cookieConsent->user_id = $userId;
            // Set IP and User Agent for GDPR compliance
            $cookieConsent->ip_address = $this->request->clientIp();
            $cookieConsent->user_agent = $this->request->getHeaderLine('User-Agent');
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            // Always create a new record for GDPR audit trail
            $newConsent = $this->CookieConsents->newEntity($this->request->getData());
            $newConsent->session_id = $sessionId;
            $newConsent->user_id = $userId;
            $newConsent->ip_address = $this->request->clientIp();
            $newConsent->user_agent = $this->request->getHeaderLine('User-Agent');

            if ($this->CookieConsents->save($newConsent)) {
                $this->Flash->success(__('Your cookie preferences have been saved.'));

                if ($this->request->is('ajax')) {
                    return $this->response->withType('application/json')
                        ->withStringBody(json_encode(['success' => true]));
                }

                return $this->redirect(['action' => 'edit']);
            }

            if ($this->request->is('ajax')) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'errors' => $cookieConsent->getErrors(),
                    ]));
            }

            $this->Flash->error(__('Your cookie preferences could not be saved. Please, try again.'));
        }

        $this->set(compact('cookieConsent'));

        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('edit');
        }

        return null;
    }
}
