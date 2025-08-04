<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Http\Response;
use App\Service\Search\UnifiedSearchService;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Products Controller
 *
 * @property \App\Model\Table\ProductsTable $Products
 */
class ProductsController extends AppController
{

    private function clearContentCache(): void
    {
        Cache::clear(config: 'content');
    }
    protected UnifiedSearchService $searchService;

    public function initialize(): void
    {
        parent::initialize();
        $this->searchService = new UnifiedSearchService();
    }


    /**
     * Dashboard method - Product overview
     */
    public function dashboard(): void
    {
        // Basic statistics
        $totalProducts = $this->Products->find()->count();
        $publishedProducts = $this->Products->find()->where(['is_published' => true])->count();
        $pendingProducts = $this->Products->find()->where(['verification_status' => 'pending'])->count();
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
                'count' => $this->Products->find()->func()->count('*')
            ])
            ->where(['manufacturer IS NOT' => null])
            ->group('manufacturer')
            ->order(['count' => 'DESC'])
            ->limit(10)
            ->toArray();

        // Popular tags
        $popularTags = $this->Products->Tags->find()
            ->select([
                'Tags.title',
                'count' => $this->Products->Tags->find()->func()->count('ProductsTags.product_id')
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
            'featuredProducts',
            'recentProducts',
            'topManufacturers',
            'popularTags'
        ));
    }

    /**
     * View method - Enhanced with related products
     */
    public function view($id = null): void
    {
        $product = $this->Products->get($id, [
            'contain' => ['Users', 'Tags', 'Articles'],
        ]);

        if (!$product) {
            throw new RecordNotFoundException(__('Product not found'));
        }
        // Get related products
        $relatedProducts = $this->Products->getRelatedProducts($id, 5);

        $this->set(compact('product', 'relatedProducts'));
    }

//     /**
//      * Add method - Enhanced with unified tagging
//      */
//     public function add(): void
//     {
//         $product = $this->Products->newEmptyEntity();

//         if ($this->request->is('post')) {
//             $data = $this->request->getData();
//             $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;

//             $product = $this->Products->patchEntity($product, $data, [
//                 'associated' => ['Tags']
//             ]);

//             if ($this->Products->save($product)) {
//                 $this->Flash->success(__('The product has been saved.'));

//                 // Queue verification job
//                 $this->queueJob('ProductVerificationJob', [
//                     'product_id' => $product->id
//                 ]);

//                 return $this->redirect(['action' => 'index']);
//             }
//             $this->Flash->error(__('The product could not be saved. Please, try again.'));
//         }
//  $product = $this->Products->newEmptyEntity();
    
//     if ($this->request->is('post')) {
//         $data = $this->request->getData();
//         $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;
        
//         $product = $this->Products->patchEntity($product, $data, [
//             'associated' => ['Tags']
//         ]);
        
//         if ($this->Products->save($product)) {
//             $this->Flash->success(__('The product has been saved.'));
            
//             // Queue verification job with compatible format
//             $this->queueJob('ProductVerificationJob', [
//                 'product_id' => $product->id
//             ]);
            
//             return $this->redirect(['action' => 'index']);
//         }
//         $this->Flash->error(__('The product could not be saved. Please, try again.'));
//     }

//     // Get form options
//     $users = $this->Products->Users->find('list', ['limit' => 200])->all();
//     $articles = $this->Products->Articles
//         ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
//         ->where(['is_published' => true])
//         ->order(['title' => 'ASC']);
//     $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

//     $this->set(compact('product', 'users', 'articles', 'tags'));
// }

    /**
     * Edit method - Enhanced with change tracking
     */
    // public function edit($id = null): void
    // {
    //     $product = $this->Products->get($id, [
    //         'contain' => ['Tags'],
    //     ]);

    //     if ($this->request->is(['patch', 'post', 'put'])) {
    //         $originalScore = $product->reliability_score;

    //         $product = $this->Products->patchEntity($product, $this->request->getData(), [
    //             'associated' => ['Tags']
    //         ]);

    //         if ($this->Products->save($product)) {
    //             $this->Flash->success(__('The product has been saved.'));

    //             // Re-verify if significant changes were made
    //             if ($this->hasSignificantChanges($product)) {
    //                 $this->queueJob('ProductVerificationJob', [
    //                     'product_id' => $product->id
    //                 ]);
    //             }

    //             return $this->redirect(['action' => 'index']);
    //         }
    //         $this->Flash->error(__('The product could not be saved. Please, try again.'));
    //     }

    //     $users = $this->Products->Users->find('list', ['limit' => 200])->all();
    //     $articles = $this->Products->Articles
    //         ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
    //         ->where(['is_published' => true])
    //         ->order(['title' => 'ASC']);
    //     $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

    //     $this->set(compact('product', 'users', 'articles', 'tags'));
    // }
    // public function edit(?string $id = null): ?Response
    // {
    //     $product = $this->Products->get($id, [
    //         'contain' => ['Tags'],
    //     ]);

    //     if ($this->request->is(['patch', 'post', 'put'])) {
    //         $data = $this->request->getData();
    //         $originalScore = $product->reliability_score;
            
    //         $product = $this->Products->patchEntity($product, $data, [
    //             'associated' => ['Tags']
    //         ]);

    //         // Handle image uploads
    //         $imageUploads = $this->request->getUploadedFiles('image_uploads') ?? [];
    //         if (!empty($imageUploads['image_uploads'])) {
    //             $product->imageUploads = $imageUploads['image_uploads'];
    //         }

    //         // Handle image unlinking
    //         $unlinkedImages = $this->request->getData('unlink_images') ?? [];
    //         $product->unlinkedImages = $unlinkedImages;

    //         $saveOptions = [];
    //         if (isset($data['regenerateTags'])) {
    //             $saveOptions['regenerateTags'] = $data['regenerateTags'];
    //         }
            
    //         if ($this->Products->save($product, $saveOptions)) {
    //             $this->clearContentCache();
    //             $this->Flash->success(__('The product has been saved.'));
                
    //             // Re-verify if significant changes were made
    //             if ($this->hasSignificantChanges($product)) {
    //                 $this->queueJob('ProductVerificationJob', [
    //                     'product_id' => $product->id
    //                 ]);
    //             }
                
    //             return $this->redirect(['action' => 'index']);
    //         }
    //         $this->Flash->error(__('The product could not be saved. Please, try again.'));
    //     }

    //     $users = $this->Products->Users->find('list', ['limit' => 200])->all();
    //     $articles = $this->Products->Articles
    //         ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
    //         ->where(['is_published' => true])
    //         ->order(['title' => 'ASC']);
    //     $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

    //     $this->set(compact('product', 'users', 'articles', 'tags'));
    //     return null;
    // }
      public function add(): ?Response
    {
        $product = $this->Products->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;
            
            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags']
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
                    'product_id' => $product->id
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
     * Verify method - Manual verification trigger
     */
    // public function verify($id = null): void
    // {
    //     $this->request->allowMethod(['post']);

    //     $this->queueJob('ProductVerificationJob', [
    //         'product_id' => $id
    //     ]);

    //     $this->Flash->success(__('Product verification has been queued.'));

    //     return $this->redirect(['action' => 'view', $id]);
    // }
     public function verify($id = null): void
    {
        $this->request->allowMethod(['post']);
        
        $this->queueJob('ProductVerificationJob', [
            'product_id' => $id
        ]);
        
        $this->Flash->success(__('Product verification has been queued.'));
        
        $this->redirect(['action' => 'view', $id]);
    }

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
     * Check if product has significant changes requiring re-verification
     */
    private function hasSignificantChanges($product): bool
    {
        return $product->isDirty(['title', 'description', 'manufacturer', 'model_number']);
    }

    /**
     * Queue a background job
     */
    private function queueJob(string $jobClass, array $data): void
    {
        $this->loadComponent('Queue.Queue');
        $this->Queue->createJob($jobClass, $data, [
            'reference' => $data['product_id'] ?? null
        ]);
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    // public function index(): ?Response
    // {
        ///////////////////////////////controller for admin products accidently merged with template of articleController (from default theme)
        ///////TODO: ERASE IF NOT NEEDED 
        //// START:
        // // TODO: Implement caching for Products index
        // $cacheKey = $this->cacheKey;
        // $products = Cache::read($cacheKey, 'content');
        // $selectedTagId = $this->request->getQuery('tag');
        // // end of imports




        // // find products with pagination
        // $query = $this->Products->find()
        //     ->contain(['Users', 'Tags', 'Articles'])
        //     ->orderBy(['Products.created' => 'DESC']);

        // // Apply filters
        // if ($this->request->getQuery('status')) {
        //     $query->where(['verification_status' => $this->request->getQuery('status')]);
        // } // Check for published status

        // if ($this->request->getQuery('published')) {
        //     $published = $this->request->getQuery('published') === '1';
        //     $query->where(['is_published' => $published]);
        // } // Check for featured products

        // if ($this->request->getQuery('featured')) {
        //     $query->where(['featured' => true]);
        // } // Check for tag filter

        // // if tag is selected, filter products by tag
        // if ($this->request->getQuery('search')) {
        //     $search = $this->request->getQuery('search');
        //     $query->where([
        //         'OR' => [
        //             'Products.title LIKE' => "%{$search}%",
        //             'Products.description LIKE' => "%{$search}%",
        //             'Products.manufacturer LIKE' => "%{$search}%",
        //             'Products.model_number LIKE' => "%{$search}%"
        //         ]
        //     ]);
        // }
        // // Paginate results
        // $this->set('products', $this->paginate($query));

        // // Get filter options
        // $tags = $this->Products->Tags
        //     ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
        //     ->order(['title' => 'ASC']);
        // // MOVED the set tags for the view to // the end of the method to ensure all filters are applied first

        // ////////// END OF TAGS FILTER

        // ///////////////////////// START OF STATUS FILTER
        // //// Get status filter
        // $statusFilter = $this->request->getQuery('status');
        // $query = $this->Products->find()
        //     ->contain(['Users', 'Articles']);

        // // Apply status filter if provided
        // $search = $this->request->getQuery('search');
        // if (!empty($search)) {
        //     $query->where([
        //         'OR' => [
        //             'Products.title LIKE' => '%' . $search . '%',
        //             'Products.slug LIKE' => '%' . $search . '%',
        //             'Products.description LIKE' => '%' . $search . '%',
        //             'Products.manufacturer LIKE' => '%' . $search . '%',
        //             'Products.model_number LIKE' => '%' . $search . '%',
        //             'Products.image LIKE' => '%' . $search . '%',
        //             'Products.alt_text LIKE' => '%' . $search . '%',
        //             'Products.verification_status LIKE' => '%' . $search . '%',
        //         ],
        //     ]);
        // }
        // $products = $this->paginate($query);

        // //TODO: CHECK CACHE WORKING
        // Cache::write($cacheKey, $products, 'content');

        // // TODO: CHECK IF THE ADDITIONAL CHECK IS NEEDED
        // // If AJAX request, return JSON response
        // if ($this->request->is('ajax')) {
        //     $this->set(compact('products', 'search'));
        //     $this->viewBuilder()->setLayout('ajax');

        //     return $this->render('search_results');
        // }
        // //TODO: CHECK CACHE WORKING
        // $recentProducts = []; // If query parameter 'page' is greater than 1, fetch recent products
        // if ($this->request->getQuery('page') > 1) {
        //     $recentProducts = $this->Products->getRecentProducts($this->cacheKey);
        // }


        // // Set variables for view
        // $this->set(compact(var_name: 'tags')); // set the tags for the vie
        // $this->set(compact('products'));

        // return null;
        //// END OF CONTROLLER MERGE

        //  $query = $this->Products->find()
        //     ->contain(['Users', 'Tags', 'Articles'])
        //     ->orderBy(['Products.created' => 'DESC']);

        // // Apply filters
        // if ($this->request->getQuery('status')) {
        //     $query->where(['verification_status' => $this->request->getQuery('status')]);
        // }

        // if ($this->request->getQuery('published')) {
        //     $published = $this->request->getQuery('published') === '1';
        //     $query->where(['is_published' => $published]);
        // }

        // if ($this->request->getQuery('featured')) {
        //     $query->where(['featured' => true]);
        // }

        // if ($this->request->getQuery('search')) {
        //     $search = $this->request->getQuery('search');
        //     $query->where([
        //         'OR' => [
        //             'Products.title LIKE' => "%{$search}%",
        //             'Products.description LIKE' => "%{$search}%",
        //             'Products.manufacturer LIKE' => "%{$search}%",
        //             'Products.model_number LIKE' => "%{$search}%"
        //         ]
        //     ]);
        // }

        // $this->set('products', $this->paginate($query));
        
        // // Get filter options
        // $tags = $this->Products->Tags
        //     ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
        //     ->order(['title' => 'ASC']);
            
        // $this->set(compact('tags'));
 
    // }

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

    /**
     * View method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
      */
    // public function view($id = null)
    // {
    //     $product = $this->Products->get($id, contain: ['Users', 'Articles', 'Tags', 'Slugs']);
    //     $this->set(compact('product'));
    // }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
//     public function add()
//     {
//         //// OLD CODE
//         // $product = $this->Products->newEmptyEntity();
//         // if ($this->request->is('post')) {
//         //     $product = $this->Products->patchEntity($product, $this->request->getData());
//         //     if ($this->Products->save($product)) {
//         //         $this->Flash->success(__('The product has been saved.'));

//         //         return $this->redirect(['action' => 'index']);
//         //     }
//         //     $this->Flash->error(__('The product could not be saved. Please, try again.'));
//         // }
//         // $users = $this->Products->Users->find('list', limit: 200)->all();
//         // $articles = $this->Products->Articles->find('list', limit: 200)->all();
//         // $tags = $this->Products->Tags->find('list', limit: 200)->all();
//         // $this->set(compact('product', 'users', 'articles', 'tags'));
//         //// END OF OLD CODE 
//          $product = $this->Products->newEmptyEntity();
    
//     if ($this->request->is('post')) {
//         $data = $this->request->getData();
//         $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;
        
//         $product = $this->Products->patchEntity($product, $data, [
//             'associated' => ['Tags']
//         ]);
        
//         if ($this->Products->save($product)) {
//             $this->Flash->success(__('The product has been saved.'));
            
//             // Queue verification job with compatible format
//             $this->queueJob('ProductVerificationJob', [
//                 'product_id' => $product->id
//             ]);
            
//             return $this->redirect(['action' => 'index']);
//         }
//         $this->Flash->error(__('The product could not be saved. Please, try again.'));
//     }

//     // Get form options
//     $users = $this->Products->Users->find('list', ['limit' => 200])->all();
//     $articles = $this->Products->Articles
//         ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
//         ->where(['is_published' => true])
//         ->order(['title' => 'ASC']);
//     $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

//     $this->set(compact('product', 'users', 'articles', 'tags'));
// }


    // public function edit($id = null)
    // {
    //     $product = $this->Products->get($id, contain: ['Tags']);
    //     if ($this->request->is(['patch', 'post', 'put'])) {
    //         $product = $this->Products->patchEntity($product, $this->request->getData());
    //         if ($this->Products->save($product)) {
    //             $this->Flash->success(__('The product has been saved.'));

    //             return $this->redirect(['action' => 'index']);
    //         }
    //         $this->Flash->error(__('The product could not be saved. Please, try again.'));
    //     }
    //     $users = $this->Products->Users->find('list', limit: 200)->all();
    //     $articles = $this->Products->Articles->find('list', limit: 200)->all();
    //     $tags = $this->Products->Tags->find('list', limit: 200)->all();
    //     $this->set(compact('product', 'users', 'articles', 'tags'));
    
     


    /**
     * Delete method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $product = $this->Products->get($id);
        if ($this->Products->delete($product)) {
            $this->Flash->success(__('The product has been deleted.'));
        } else {
            $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
