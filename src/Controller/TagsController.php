<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

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
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['index', 'view', 'viewBySlug']);

        return null;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): void
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
    public function view(?string $id = null): void
    {
        $tag = $this->Tags->find()
            ->where(['Tags.id' => $id])
            ->contain(['Articles' => [
                'Users' => ['fields' => ['id', 'username']],
                ],
            ], true)
            ->first();

        $this->set(compact('tag'));
    }

    /**
     * Displays a tag and its associated articles based on the tag's slug.
     *
     * This method retrieves a tag by its slug and loads associated articles
     * with their authors. It selects specific fields for efficiency and
     * throws an exception if the tag is not found.
     *
     * @param string $slug The unique slug of the tag to retrieve.
     * @throws \Cake\Http\Exception\NotFoundException If the tag is not found.
     * @return void
     */
    public function viewBySlug(string $slug): void
    {
        $query = $this->Tags->find()
            ->contain(['Articles' => function ($q) {
                return $q->select(['id', 'title', 'slug', 'user_id', 'created'])
                    ->contain(['Users' => function ($q) {
                        return $q->select(['id', 'username']);
                    }]);
            }])
            ->where(['Tags.slug' => $slug]);

        $tag = $query->first();

        if (!$tag) {
            throw new NotFoundException(__('Tag not found'));
        }

        // Set the tag data for the view
        $this->set(compact('tag'));
    }
}
