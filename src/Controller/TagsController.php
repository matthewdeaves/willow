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
                    ->where(['Articles.is_published' => true])
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
