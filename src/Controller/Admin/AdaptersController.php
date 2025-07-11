<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Exception;

/**
 * Adapters Controller
 *
 * Handles CRUD operations for adapters, including pages and blog posts.
 *
 * @property \App\Model\Table\AdaptersTable $Adapters
 */
class AdaptersController extends AppController
{
    /**
     * Clears the content cache (used for both adapters and tags)
     *
     * @return void
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Retrieves a hierarchical list of adapters that are marked as pages.
     *
     * @return void
     */
    public function treeIndex(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $conditions = [
            'Adapters.kind' => 'page',
        ];

        if ($statusFilter === '1') {
            $conditions['Adapters.is_published'] = '1';
        } elseif ($statusFilter === '0') {
            $conditions['Adapters.is_published'] = '0';
        }

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $conditions['OR'] = [
                    'Adapters.title LIKE' => '%' . $search . '%',
                    'Adapters.slug LIKE' => '%' . $search . '%',
                    'Adapters.body LIKE' => '%' . $search . '%',
                    'Adapters.meta_title LIKE' => '%' . $search . '%',
                    'Adapters.meta_description LIKE' => '%' . $search . '%',
                    'Adapters.meta_keywords LIKE' => '%' . $search . '%',
                ];
            }
            $adapters = $this->Adapters->getTree($conditions, [
                'slug',
                'created',
                'modified',
                'is_published',
            ]);

            $this->set(compact('adapters'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('tree_index_search_results');
        }

        $adapters = $this->Adapters->getTree($conditions, [
            'slug',
            'created',
            'modified',
            'view_count',
            'is_published',
        ]);
        $this->set(compact('adapters'));

        return null;
    }

    /**
     * Updates the tree structure of adapters.
     *
     * @return \Cake\Http\Response|null The JSON response indicating success or failure.
     * @throws \Exception If an error occurs during the reordering process.
     */
    public function updateTree(): ?Response
    {
        $this->request->allowMethod(['post', 'put']);
        $data = $this->request->getData();

        try {
            $result = $this->Adapters->reorder($data);
            $this->clearContentCache();

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'result' => $result]));
        } catch (Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Displays a list of adapters with search functionality.
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');

        $query = $this->Adapters->find()
            ->select([
                'Adapters.id',
                'Adapters.user_id',
                'Adapters.title',
                'Adapters.slug',
                'Adapters.image',
                'Adapters.dir',
                'Adapters.alt_text',
                'Adapters.created',
                'Adapters.modified',
                'Adapters.published',
                'Adapters.is_published',
                'Adapters.body',
                'Adapters.summary',
                'Adapters.meta_title',
                'Adapters.meta_description',
                'Adapters.meta_keywords',
                'Adapters.linkedin_description',
                'Adapters.facebook_description',
                'Adapters.instagram_description',
                'Adapters.twitter_description',
                'Adapters.word_count',
                'Adapters.view_count',
                'Users.id',
                'Users.username',
            ])
            ->leftJoinWith('Users')
            ->leftJoinWith('PageViews')
            ->where(['Adapters.kind' => 'adapter'])
            ->groupBy([
                'Adapters.id',
                'Adapters.user_id',
                'Adapters.title',
                'Adapters.slug',
                'Adapters.created',
                'Adapters.modified',
                'Users.id',
                'Users.username',
            ])
            ->orderBy(['Adapters.created' => 'DESC']);

        if ($statusFilter !== null) {
            $query->where(['Adapters.is_published' => (int)$statusFilter]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Adapters.title LIKE' => '%' . $search . '%',
                    'Adapters.slug LIKE' => '%' . $search . '%',
                    'Adapters.body LIKE' => '%' . $search . '%',
                    'Adapters.meta_title LIKE' => '%' . $search . '%',
                    'Adapters.meta_description LIKE' => '%' . $search . '%',
                    'Adapters.meta_keywords LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $adapters = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('adapters', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('adapters'));

        return null;
    }

    /**
     * Displays details of a specific adapter.
     *
     * @param string|null $id Adapter id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $adapter = $this->Adapters->get($id, contain: [
            'Users',
            'PageViews',
            'Tags',
            'Images',
            'Slugs',
            'Comments',
        ]);

        if (!$adapter) {
            throw new RecordNotFoundException(__('Adapter not found'));
        }

        $this->set(compact('adapter'));
    }

    /**
     * Adds a new adapter.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $adapter = $this->Adapters->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['kind'] = $this->request->getQuery('kind', 'adapter');
            $adapter = $this->Adapters->patchEntity($adapter, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $adapter->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Adapters->save($adapter)) {
                $this->clearContentCache();
                $this->Flash->success(__('The adapter has been saved.'));

                // Redirect to treeIndex if is page, otherwise to index
                if ($adapter->kind == 'page') {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The adapter could not be saved. Please, try again.'));
        }

        // Fetch parent adapters if 'kind' is page
        $parentAdapters = [];
        if ($this->request->getQuery('kind') == 'page') {
            $parentAdapters = $this->Adapters->find('list')
                ->where(['kind' => 'page'])
                ->all();
        }

        $users = $this->Adapters->Users->find('list', limit: 200)->all();
        $tags = $this->Adapters->Tags->find('list', limit: 200)->all();
        $token = $this->request->getAttribute('csrfToken');
        $this->set(compact('adapter', 'users', 'tags', 'token', 'parentAdapters'));

        return null;
    }

    /**
     * Edits an existing adapter.
     *
     * @param string|null $id Adapter ID.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $adapter = $this->Adapters->get($id, contain: ['Tags', 'Images']);

        if (!empty($adapter->body) && empty($adapter->markdown)) {
            $adapter->markdown = $adapter->body;
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $data['kind'] = $this->request->getQuery('kind', 'adapter');
            $adapter = $this->Adapters->patchEntity($adapter, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads') ?? [];
            if (!empty($imageUploads['image_uploads'])) {
                $adapter->imageUploads = $imageUploads['image_uploads'];
            }

            // Handle image unlinking
            $unlinkedImages = $this->request->getData('unlink_images') ?? [];
            $adapter->unlinkedImages = $unlinkedImages;

            $saveOptions = [];
            if (isset($data['regenerateTags'])) {
                $saveOptions['regenerateTags'] = $data['regenerateTags'];
            }

            if ($this->Adapters->save($adapter, $saveOptions)) {
                $this->clearContentCache();
                $this->Flash->success(__('The adapter has been saved.'));

                // Redirect to treeIndex if kind is page, otherwise to index
                if ($adapter->kind == 'page') {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The adapter could not be saved. Please, try again.'));
        }

        // Fetch parent adapters if 'kind' is page
        $parentAdapters = [];
        if ($this->request->getQuery('kind') == 'page') {
            $parentAdapters = $this->Adapters->find('list')
                ->where([
                    'kind' => 'page',
                    'id !=' => $id,
                    ])
                ->all();
        }

        $users = $this->Adapters->Users->find('list', limit: 200)->all();
        $tags = $this->Adapters->Tags->find('list', limit: 200)->all();
        $this->set(compact('adapter', 'users', 'tags', 'parentAdapters'));

        return null;
    }

    /**
     * Deletes an adapter.
     *
     * @param string|null $id Adapter ID.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $adapter = $this->Adapters->get($id);
        if ($this->Adapters->delete($adapter)) {
            $this->clearContentCache();

            $this->Flash->success(__('The adapter has been deleted.'));
        } else {
            $this->Flash->error(__('The adapter could not be deleted. Please, try again.'));
        }

        // Check if 'kind' is in the request parameters
        if ($this->request->getData('kind') == 'page') {
            $this->redirect(['action' => 'treeIndex']);
        }

        $action = $this->request->getQuery('kind') == 'page' ? 'tree-index' : 'index';

        $this->redirect(['action' => $action]);
    }
}
