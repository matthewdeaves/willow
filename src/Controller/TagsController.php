<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Event\EventInterface;

/**
 * Tags Controller
 *
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    /**
     * beforeFilter method
     *
     * @param \Cake\Event\EventInterface $event The event object that contains the request and response objects.
     * @return void
     * @throws \Cake\Http\Exception\RedirectException If a redirect is necessary.
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['index', 'view']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Tags->find();
        $tags = $this->paginate($query);

        $this->set(compact('tags'));
    }

    /**
     * View method
     *
     * @param string|null $id Tag id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $tag = $this->Tags->find()
            ->where(['Tags.id' => $id])
            ->contain(['Articles' => [
                'Users' => ['fields' => ['id', 'username']]
                ]
            ], true)
            ->first();

        $this->set(compact('tag'));
    }
}
