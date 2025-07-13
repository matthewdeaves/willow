<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Exception;

/**
 * Products Controller
 *
 * Handles CRUD operations for products, including pages and blog posts.
 *
 * @property \App\Model\Table\ProductsTable $Products
 */
class ProductsController extends AppController
{
    /**
     * Clears the content cache (used for both products and tags)
     *
     * @return void
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Retrieves a hierarchical list of products that are marked as pages.
     *
     * @return void
     */
    public function treeIndex(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $conditions = [
            'Products.kind' => 'page',
        ];

        if ($statusFilter === '1') {
            $conditions['Products.is_published'] = '1';
        } elseif ($statusFilter === '0') {
            $conditions['Products.is_published'] = '0';
        }

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $conditions['OR'] = [
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.slug LIKE' => '%' . $search . '%',
                    'Products.body LIKE' => '%' . $search . '%',
                    'Products.meta_title LIKE' => '%' . $search . '%',
                    'Products.meta_description LIKE' => '%' . $search . '%',
                    'Products.meta_keywords LIKE' => '%' . $search . '%',
                ];
            }
            $products = $this->Products->getTree($conditions, [
                'slug',
                'created',
                'modified',
                'is_published',
            ]);

            $this->set(compact('products'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('tree_index_search_results');
        }

        $products = $this->Products->getTree($conditions, [
            'slug',
            'created',
            'modified',
            'view_count',
            'is_published',
        ]);
        $this->set(compact('products'));

        return null;
    }

    /**
     * Updates the tree structure of products.
     *
     * @return \Cake\Http\Response|null The JSON response indicating success or failure.
     * @throws \Exception If an error occurs during the reordering process.
     */
    public function updateTree(): ?Response
    {
        $this->request->allowMethod(['post', 'put']);
        $data = $this->request->getData();

        try {
            $result = $this->Products->reorder($data);
            $this->clearContentCache();

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'result' => $result]));
        } catch (Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Displays a list of products with search functionality.
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');

        $query = $this->Products->find()
            ->select([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.image',
                'Products.dir',
                'Products.alt_text',
                'Products.created',
                'Products.modified',
                'Products.published',
                'Products.is_published',
                'Products.body',
                'Products.summary',
                'Products.meta_title',
                'Products.meta_description',
                'Products.meta_keywords',
                'Products.linkedin_description',
                'Products.facebook_description',
                'Products.instagram_description',
                'Products.twitter_description',
                'Products.word_count',
                'Products.view_count',
                'Users.id',
                'Users.username',
            ])
            ->leftJoinWith('Users')
            ->leftJoinWith('PageViews')
            ->where(['Products.kind' => 'product'])
            ->groupBy([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->orderBy(['Products.created' => 'DESC']);

        if ($statusFilter !== null) {
            $query->where(['Products.is_published' => (int)$statusFilter]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.slug LIKE' => '%' . $search . '%',
                    'Products.body LIKE' => '%' . $search . '%',
                    'Products.meta_title LIKE' => '%' . $search . '%',
                    'Products.meta_description LIKE' => '%' . $search . '%',
                    'Products.meta_keywords LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $products = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('products', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('products'));

        return null;
    }

    /**
     * Displays details of a specific product.
     *
     * @param string|null $id Product id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $product = $this->Products->get($id, contain: [
            'Users',
            'PageViews',
            'Tags',
            'Images',
            'Slugs',
            'Comments',
        ]);

        if (!$product) {
            throw new RecordNotFoundException(__('Product not found'));
        }

        $this->set(compact('product'));
    }

    /**
     * Adds a new product.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $product = $this->Products->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['kind'] = $this->request->getQuery('kind', 'product');
            $product = $this->Products->patchEntity($product, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Products->save($product)) {
                $this->clearContentCache();
                $this->Flash->success(__('The product has been saved.'));

                // Redirect to treeIndex if is page, otherwise to index
                if ($product->kind == 'page') {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        // Fetch parent products if 'kind' is page
        $parentProducts = [];
        if ($this->request->getQuery('kind') == 'page') {
            $parentProducts = $this->Products->find('list')
                ->where(['kind' => 'page'])
                ->all();
        }

        $users = $this->Products->Users->find('list', limit: 200)->all();
        $tags = $this->Products->Tags->find('list', limit: 200)->all();
        $token = $this->request->getAttribute('csrfToken');
        $this->set(compact('product', 'users', 'tags', 'token', 'parentProducts'));

        return null;
    }

    /**
     * Edits an existing product.
     *
     * @param string|null $id Product ID.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $product = $this->Products->get($id, contain: ['Tags', 'Images']);

        if (!empty($product->body) && empty($product->markdown)) {
            $product->markdown = $product->body;
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $data['kind'] = $this->request->getQuery('kind', 'product');
            $product = $this->Products->patchEntity($product, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads') ?? [];
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            // Handle image unlinking
            $unlinkedImages = $this->request->getData('unlink_images') ?? [];
            $product->unlinkedImages = $unlinkedImages;

            $saveOptions = [];
            if (isset($data['regenerateTags'])) {
                $saveOptions['regenerateTags'] = $data['regenerateTags'];
            }

            if ($this->Products->save($product, $saveOptions)) {
                $this->clearContentCache();
                $this->Flash->success(__('The product has been saved.'));

                // Redirect to treeIndex if kind is page, otherwise to index
                if ($product->kind == 'page') {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        // Fetch parent products if 'kind' is page
        $parentProducts = [];
        if ($this->request->getQuery('kind') == 'page') {
            $parentProducts = $this->Products->find('list')
                ->where([
                    'kind' => 'page',
                    'id !=' => $id,
                    ])
                ->all();
        }

        $users = $this->Products->Users->find('list', limit: 200)->all();
        $tags = $this->Products->Tags->find('list', limit: 200)->all();
        $this->set(compact('product', 'users', 'tags', 'parentProducts'));

        return null;
    }

    /**
     * Deletes an product.
     *
     * @param string|null $id Product ID.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $product = $this->Products->get($id);
        if ($this->Products->delete($product)) {
            $this->clearContentCache();

            $this->Flash->success(__('The product has been deleted.'));
        } else {
            $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        }

        // Check if 'kind' is in the request parameters
        if ($this->request->getData('kind') == 'page') {
            $this->redirect(['action' => 'treeIndex']);
        }

        $action = $this->request->getQuery('kind') == 'page' ? 'tree-index' : 'index';

        $this->redirect(['action' => $action]);
    }
}
