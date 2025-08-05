<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Http\Response;
use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;

class ProductsController extends AppController
{

        
    /**
     * Dashboard method - Product overview and statistics
     *
     * @return void
     */
    public function dashboard(): void
    {
        // Basic statistics
        $totalProducts = $this->Products->find()->count();
        $publishedProducts = $this->Products->find()->where(['is_published' => true])->count();
        $pendingProducts = $this->Products->find()->where(['verification_status' => 'pending'])->count();
        $approvedProducts = $this->Products->find()->where(['verification_status' => 'approved'])->count();
        $featuredProducts = $this->Products->find()->where(['featured' => true])->count();

        // Recent products
        $recentProducts = $this->Products->find()
            ->contain(['Users', 'Tags'])
            ->order(['created' => 'DESC'])
            ->limit(10)
            ->toArray();

        // Top manufacturers
        $topManufacturers = $this->Products->find()
            ->select([
                'manufacturer',
                'count' => $this->Products->find()->func()->count('*'),
            ])
            ->where(['manufacturer IS NOT' => null, 'manufacturer !=' => ''])
            ->group('manufacturer')
            ->order(['count' => 'DESC'])
            ->limit(10)
            ->toArray();

        // Popular tags
        $popularTags = $this->Products->Tags->find()
            ->select([
                'Tags.title',
                'count' => $this->Products->Tags->find()->func()->count('ProductsTags.product_id'),
            ])
            ->leftJoinWith('Products')
            ->group('Tags.id')
            ->order(['count' => 'DESC'])
            ->limit(10)
            ->toArray();

        $this->set(compact(
            'totalProducts',
            'publishedProducts',
            'pendingProducts',
            'approvedProducts',
            'featuredProducts',
            'recentProducts',
            'topManufacturers',
            'popularTags',
        ));
    }

    /**
     * Index method - Product listing with search and filtering
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
                'Products.description',
                'Products.manufacturer',
                'Products.model_number',
                'Products.price',
                'Products.currency',
                'Products.image',
                'Products.alt_text',
                'Products.is_published',
                'Products.featured',
                'Products.verification_status',
                'Products.reliability_score',
                'Products.view_count',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->leftJoinWith('Users')
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

        // Status filtering
        if ($statusFilter !== null) {
            if ($statusFilter === 'published') {
                $query->where(['Products.is_published' => true]);
            } elseif ($statusFilter === 'unpublished') {
                $query->where(['Products.is_published' => false]);
            } elseif (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
                $query->where(['Products.verification_status' => $statusFilter]);
            }
        }

        // Search functionality
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.description LIKE' => '%' . $search . '%',
                    'Products.manufacturer LIKE' => '%' . $search . '%',
                    'Products.model_number LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        // Featured filter
        if ($this->request->getQuery('featured')) {
            $query->where(['Products.featured' => true]);
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

    public function index2(): ?Response
    {
    $statusFilter = $this->request->getQuery('status');
        $query = $this->Products->find()
            ->select([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.description',
                'Products.manufacturer',
                'Products.model_number',
                'Products.price',
                'Products.currency',
                'Products.image',
                'Products.alt_text',
                'Products.is_published',
                'Products.featured',
                'Products.verification_status',
                'Products.reliability_score',
                'Products.view_count',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->leftJoinWith('Users')
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

        // Status filtering
        if ($statusFilter !== null) {
            if ($statusFilter === 'published') {
                $query->where(['Products.is_published' => true]);
            } elseif ($statusFilter === 'unpublished') {
                $query->where(['Products.is_published' => false]);
            } elseif (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
                $query->where(['Products.verification_status' => $statusFilter]);
            }
        }

        // Search functionality
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.description LIKE' => '%' . $search . '%',
                    'Products.manufacturer LIKE' => '%' . $search . '%',
                    'Products.model_number LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        // Featured filter
        if ($this->request->getQuery('featured')) {
            $query->where(['Products.featured' => true]);
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
     * View method - Display single product details
     *
     * @param string|null $id Product id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $product = $this->Products->get($id, [
            'contain' => [
                'Users',
                'Tags',
                'Articles',
            ],
        ]);

        if (!$product) {
            throw new RecordNotFoundException(__('Product not found'));
        }

        // Get related products
        $relatedProducts = $this->Products->getRelatedProducts($id, 5);

        $this->set(compact('product', 'relatedProducts'));
    }

    /**
     * View2 method - Display single product details (copy)
     *
     * @param string|null $id Product id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view2(?string $id = null): void
    {
        $product = $this->Products->get($id, [
            'contain' => [
                'Users',
                'Tags',
                'Articles',
            ],
        ]);

        if (!$product) {
            throw new RecordNotFoundException(__('Product not found'));
        }

        // Get related products
        $relatedProducts = $this->Products->getRelatedProducts($id, 5);

        $this->set(compact('product', 'relatedProducts'));
    }

    /**
     * Add2 method - Create new product (copy)
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add2(): ?Response
    {
        $product = $this->Products->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;

            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Products->save($product)) {
                $this->clearContentCache();
                $this->Flash->success(__('The product has been saved.'));

                // Queue verification job
                $this->queueJob('ProductVerificationJob', [
                    'product_id' => $product->id,
                ]);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        // Get form options
        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->order(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();
        $token = $this->request->getAttribute('csrfToken');

        $this->set(compact('product', 'users', 'articles', 'tags', 'token'));

        return null;
    }

    /**
     * Edit2 method - Modify existing product (copy)
     *
     * @param string|null $id Product ID.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit2(?string $id = null): ?Response
    {
        $product = $this->Products->get($id, [
            'contain' => ['Tags'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $originalScore = $product->reliability_score;

            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

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

                // Re-verify if significant changes were made
                if ($this->hasSignificantChanges($product)) {
                    $this->queueJob('ProductVerificationJob', [
                        'product_id' => $product->id,
                    ]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->order(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

        $this->set(compact('product', 'users', 'articles', 'tags'));

        return null;
    }

    /**
     * Add method - Create new product
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $product = $this->Products->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;

            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Products->save($product)) {
                $this->clearContentCache();
                $this->Flash->success(__('The product has been saved.'));

                // Queue verification job
                $this->queueJob('ProductVerificationJob', [
                    'product_id' => $product->id,
                ]);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        // Get form options
        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->order(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();
        $token = $this->request->getAttribute('csrfToken');

        $this->set(compact('product', 'users', 'articles', 'tags', 'token'));

        return null;
    }

    /**
     * Edit method - Modify existing product
     *
     * @param string|null $id Product ID.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $product = $this->Products->get($id, [
            'contain' => ['Tags'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $originalScore = $product->reliability_score;

            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

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

                // Re-verify if significant changes were made
                if ($this->hasSignificantChanges($product)) {
                    $this->queueJob('ProductVerificationJob', [
                        'product_id' => $product->id,
                    ]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->order(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

        $this->set(compact('product', 'users', 'articles', 'tags'));

        return null;
    }

    /**
     * Delete method - Remove product
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

        $this->redirect(['action' => 'index']);
    }

    /**
     * Verify method - Manual verification trigger
     */
    public function verify($id = null): void
    {
        $this->request->allowMethod(['post']);

        $this->queueJob('ProductVerificationJob', [
            'product_id' => $id,
        ]);

        $this->Flash->success(__('Product verification has been queued.'));

        $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id = null): void
    {
        $this->request->allowMethod(['post']);

        $product = $this->Products->get($id);
        $product->featured = !$product->featured;

        if ($this->Products->save($product)) {
            $this->clearContentCache();
            $status = $product->featured ? 'featured' : 'unfeatured';
            $this->Flash->success(__('Product has been {0}.', $status));
        } else {
            $this->Flash->error(__('Could not update product status.'));
        }

        $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Toggle published status
     */
    public function togglePublished($id = null): void
    {
        $this->request->allowMethod(['post']);

        $product = $this->Products->get($id);
        $product->is_published = !$product->is_published;

        if ($this->Products->save($product)) {
            $this->clearContentCache();
            $status = $product->is_published ? 'published' : 'unpublished';
            $this->Flash->success(__('Product has been {0}.', $status));
        } else {
            $this->Flash->error(__('Could not update product status.'));
        }

        $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Check if product has significant changes requiring re-verification
     */
    private function hasSignificantChanges($product): bool
    {
        return $product->isDirty(['title', 'description', 'manufacturer', 'model_number']);
    }

    /**
     * Queue a background job using the proper Message format
     */
    private function queueJob(string $jobClass, array $data): void
    {
        $this->loadComponent('Queue.Queue');

        // Create job with proper Message format
        $this->Queue->createJob($jobClass, $data, [
            'reference' => $data['product_id'] ?? null,
        ]);
    }

    /**
     * Clear content cache after operations
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }
}
