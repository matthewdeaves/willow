<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\Cache\Cache;

/**
 * Products Controller - Frontend
 *
 * @property \App\Model\Table\ProductsTable $Products
 */
class ProductsController extends AppController
{
    /**
     * beforeFilter callback.
     *
     * Allow unauthenticated access to certain public actions
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to index, view, and quiz actions
        $this->Authentication->addUnauthenticatedActions(['index', 'view', 'quiz']);
    }

    /**
     * Index method - Browse published products
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        // Get only published products for public viewing
        $query = $this->Products->getPublishedProducts();
        
        // Search functionality
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query = $this->Products->searchProducts($search);
        }
        
        // Filtering options
        $manufacturer = $this->request->getQuery('manufacturer');
        if (!empty($manufacturer)) {
            $query->where(['Products.manufacturer LIKE' => '%' . $manufacturer . '%']);
        }
        
        $tag = $this->request->getQuery('tag');
        if (!empty($tag)) {
            $query->matching('Tags', function ($q) use ($tag) {
                return $q->where(['Tags.slug' => $tag]);
            });
        }
        
        $featured = $this->request->getQuery('featured');
        if ($featured) {
            $query->where(['Products.featured' => true]);
        }

        $products = $this->paginate($query);
        
        // Get filter options for the sidebar
        $manufacturers = $this->Products->find()
            ->select(['manufacturer'])
            ->where([
                'manufacturer IS NOT' => null,
                'manufacturer !=' => '',
                'is_published' => true
            ])
            ->groupBy(['manufacturer'])
            ->orderBy(['manufacturer' => 'ASC'])
            ->toArray();
            
        $tags = $this->Products->Tags->find()
            ->matching('Products', function ($q) {
                return $q->where(['Products.is_published' => true]);
            })
            ->orderBy(['Tags.title' => 'ASC'])
            ->limit(20)
            ->toArray();

        if ($this->request->is('ajax')) {
            $this->set(compact('products', 'search'));
            $this->viewBuilder()->setLayout('ajax');
            return $this->render('search_results');
        }

        $this->set(compact('products', 'manufacturers', 'tags', 'search', 'manufacturer', 'tag', 'featured'));
        return null;
    }

    /**
     * View method - Display single product details
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): ?Response
    {
        $product = $this->Products->get($id, [
            'contain' => ['Users', 'Articles', 'Tags']
        ]);
        
        // Only show published products to public
        if (!$product->is_published) {
            throw new \Cake\Http\Exception\NotFoundException(__('Product not found.'));
        }
        
        // Increment view count
        $this->Products->incrementViewCount($id);
        
        // Get related products
        $relatedProducts = $this->Products->getRelatedProducts($id, 4);
        
        $this->set(compact('product', 'relatedProducts'));
        return null;
    }

    /**
     * Quiz method - Interactive adapter finder
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function quiz(): ?Response
    {
        // Get quiz settings
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        
        $quizEnabled = $settingsTable->find()
            ->where(['category' => 'Products', 'key_name' => 'quiz_enabled'])
            ->first();
            
        $quizConfig = $settingsTable->find()
            ->where(['category' => 'Products', 'key_name' => 'quiz_config_json'])
            ->first();
        
        if (!$quizEnabled || $quizEnabled->value !== '1') {
            $this->Flash->error(__('The adapter finder quiz is currently not available.'));
            return $this->redirect(['action' => 'index']);
        }
        
        $config = [];
        if ($quizConfig && !empty($quizConfig->value)) {
            $config = json_decode($quizConfig->value, true) ?: [];
        }
        
        // Handle quiz submission
        if ($this->request->is('post')) {
            $answers = $this->request->getData('answers', []);
            $recommendations = $this->processQuizAnswers($answers, $config);
            
            $this->set(compact('config', 'answers', 'recommendations'));
            return $this->render('quiz_results');
        }
        
        $this->set(compact('config'));
        return null;
    }

    /**
     * Add method - User product submission form
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        // Check if public submissions are enabled
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        
        $publicSubmissionsEnabled = $settingsTable->find()
            ->where(['category' => 'Products', 'key_name' => 'enable_public_submissions'])
            ->first();
            
        if (!$publicSubmissionsEnabled || $publicSubmissionsEnabled->value !== '1') {
            $this->Flash->error(__('Public product submissions are currently not enabled.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Get form settings
        $formSettings = $this->getProductFormSettings();
        
        $product = $this->Products->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Set user ID and submission defaults
            $identity = $this->getRequest()->getAttribute('identity');
            $data['user_id'] = $identity ? $identity->id : null;
            $data['is_published'] = false; // Always start as unpublished
            $data['verification_status'] = $formSettings['default_status'] ?? 'pending';
            $data['featured'] = false;
            
            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags']
            ]);
            
            // Validate required fields based on settings
            $requiredFields = explode(',', $formSettings['required_fields'] ?? 'title');
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                $field = trim($field);
                if (empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                $this->Flash->error(__('Please fill in all required fields: {0}', implode(', ', $missingFields)));
            } else {
                // Handle image upload
                $imageUploads = $this->request->getUploadedFiles('image_uploads');
                if (!empty($imageUploads['image_uploads'])) {
                    $product->imageUploads = $imageUploads['image_uploads'];
                }
                
                if ($this->Products->save($product)) {
                    $this->clearContentCache();
                    
                    $successMessage = $formSettings['success_message'] ?? 'Your product has been submitted and is awaiting review.';
                    $this->Flash->success(__($successMessage));
                    
                    // Send notification email if configured
                    $this->sendSubmissionNotification($product, $formSettings);
                    
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The product could not be saved. Please, try again.'));
            }
        }
        
        // Get form options
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();
        
        $this->set(compact('product', 'tags', 'formSettings'));
        return null;
    }
    
    /**
     * Get product form settings from database
     */
    private function getProductFormSettings(): array
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        
        $settings = $settingsTable->find()
            ->where(['category' => 'Products'])
            ->toArray();
            
        $formSettings = [];
        foreach ($settings as $setting) {
            $formSettings[$setting->key_name] = $setting->value;
        }
        
        return $formSettings;
    }
    
    /**
     * Process quiz answers and return product recommendations
     */
    private function processQuizAnswers(array $answers, array $config): array
    {
        if (empty($config['questions']) || empty($config['scoring'])) {
            return [];
        }
        
        $scores = [];
        $reasoning = [];
        
        // Process each answer against scoring rules
        foreach ($answers as $questionId => $answer) {
            if (isset($config['scoring'][$answer])) {
                $rules = $config['scoring'][$answer];
                foreach ($rules as $rule) {
                    if (strpos($rule, 'tag:') === 0) {
                        $tagSlug = substr($rule, 4);
                        $scores['tags'][] = $tagSlug;
                        $reasoning[] = "Based on your selection, we recommend products tagged with '{$tagSlug}'";
                    } elseif (strpos($rule, 'manufacturer:') === 0) {
                        $manufacturer = substr($rule, 13);
                        $scores['manufacturers'][] = $manufacturer;
                        $reasoning[] = "Your needs align with products from {$manufacturer}";
                    }
                }
            }
        }
        
        // Find matching products
        $query = $this->Products->getPublishedProducts();
        
        if (!empty($scores['tags'])) {
            $query->matching('Tags', function ($q) use ($scores) {
                return $q->where(['Tags.slug IN' => $scores['tags']]);
            });
        }
        
        if (!empty($scores['manufacturers'])) {
            $manufacturerConditions = [];
            foreach ($scores['manufacturers'] as $manufacturer) {
                $manufacturerConditions[] = ['Products.manufacturer LIKE' => '%' . $manufacturer . '%'];
            }
            $query->where(['OR' => $manufacturerConditions]);
        }
        
        $products = $query->limit(10)->toArray();
        
        return [
            'products' => $products,
            'reasoning' => $reasoning,
            'scores' => $scores
        ];
    }
    
    /**
     * Send notification email about new product submission
     */
    private function sendSubmissionNotification($product, array $formSettings): void
    {
        $notificationEmail = $formSettings['notification_email'] ?? '';
        
        if (empty($notificationEmail) || $notificationEmail === '0') {
            return;
        }
        
        // In a real implementation, you would send an email here
        // For now, just log it
        $this->log(sprintf(
            'New product submission: "%s" by user %s (ID: %s). Notification should be sent to: %s',
            $product->title,
            $product->user->username ?? 'Unknown',
            $product->user_id ?? 'Unknown',
            $notificationEmail
        ), 'info');
    }
    
    /**
     * Clear content cache after operations
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }
}
