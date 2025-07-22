<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\PageViewsTable;
use App\Model\Table\SlugsTable;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Routing\Router;

/**
 * Products Controller
 *
 * Manages product-related operations including viewing, listing, and commenting.
 *
 * @property \App\Model\Table\ProductsTable $Products
 * @property \App\Model\Table\PageViewsTable $PageViews
 * @property \App\Model\Table\SlugsTable $Slugs
 */
class ProductsController extends AppController
{
    /**
     * Default pagination configuration.
     *
     * Defines the default settings for paginating product records.
     * The limit determines how many products are displayed per page.
     *
     * @var array<string, mixed> $paginate Configuration array for pagination
     */
    protected array $paginate = [
        'limit' => 6,
    ];

    /**
     * PageViews Table
     *
     * @var \App\Model\Table\PageViewsTable $PageViews
     *
     * This property holds an instance of the PageViewsTable class.
     * It is used to interact with the page_views table in the database.
     * The PageViewsTable provides methods for querying and manipulating
     * page view data, such as tracking product views and retrieving view statistics.
     */
    protected PageViewsTable $PageViews;

    /**
     * Slugs Table
     *
     * @var \App\Model\Table\SlugsTable $Slugs
     *
     * This property holds an instance of the SlugsTable class.
     * It is used to interact with the slugs table in the database.
     * The SlugsTable provides methods for querying and manipulating
     * slug data, such as creating new slugs, finding the latest slug
     * for an product, and managing slug history.
     */
    protected SlugsTable $Slugs;

    /**
     * Initializes the controller.
     *
     * Sets up the Slugs and PageViews table instances.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Slugs = $this->fetchTable('Slugs');
        $this->PageViews = $this->fetchTable('PageViews');
    }

    /**
     * Configures authentication for specific actions.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions(['view', 'index', 'viewBySlug', 'pageIndex']);

        if ($this->request->getParam('action') === 'addComment' && $this->request->is('post')) {
            $result = $this->Authentication->getResult();
            if (!$result || !$result->isValid()) {
                $session = $this->request->getSession();
                $session->write('Comment.formData', $this->request->getData());
            }
        }
    }

    /**
     * Displays the page index.
     *
     * Retrieves and sets the root page product and a threaded list of all page products.
     *
     * @return void
     */
    public function pageIndex(): void
    {
        $product = $this->Products->find()
            ->orderBy(['lft' => 'ASC'])
            ->where([
                'Products.kind' => 'page',
                'Products.is_published' => 1,
            ])
            ->first();

        $products = $this->Products->getTree();

        $this->set(compact('product', 'products'));
    }

    /**
     * Displays a paginated list of published products.
     *
     * Retrieves published products with optional tag filtering.
     *
     * @return void
     */
    public function index(): void
    {
        $cacheKey = $this->cacheKey;
        $products = Cache::read($cacheKey, 'content');
        $selectedTagId = $this->request->getQuery('tag');

        if (!$products) {
            $query = $this->Products->find()
                ->where([
                    'Products.kind' => 'product',
                    'Products.is_published' => 1,
                ])
                ->contain(['Users', 'Tags'])
                ->orderBy(['Products.published' => 'DESC']);

            if ($selectedTagId) {
                $query->matching('Tags', function ($q) use ($selectedTagId) {
                    return $q->where(['Tags.id' => $selectedTagId]);
                });
            }

            $year = $this->request->getQuery('year');
            $month = $this->request->getQuery('month');

            if ($year) {
                $conditions = ['YEAR(Products.published)' => $year];
                if ($month) {
                    $conditions['MONTH(Products.published)'] = $month;
                }
                $query->where($conditions);
            }

            $products = $this->paginate($query);
            Cache::write($cacheKey, $products, 'content');
        }

        $recentProducts = [];
        if ($this->request->getQuery('page') > 1) {
            $recentProducts = $this->Products->getRecentProducts($this->cacheKey);
        }

        $this->set(compact(
            'products',
            'selectedTagId',
            'recentProducts',
        ));

        $this->viewBuilder()->setLayout('product_index');
    }

    /**
     * Displays an product by its slug.
     *
     * Retrieves an product using the provided slug, handling caching and redirects.
     *
     * @param string $slug The slug of the product to view.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\NotFoundException If the product is not found.
     */
    public function viewBySlug(string $slug): ?Response
    {
        $cacheKey = $slug . $this->cacheKey;
        $product = Cache::read($cacheKey, 'content');

        if (empty($product)) {
            // If not in cache, we need to check if this is the latest slug
            $slugEntity = $this->Slugs->find()
                ->where([
                    'slug' => $slug,
                    'model' => 'Products',
                    ])
                ->orderBy(['created' => 'DESC'])
                ->select(['foreign_key'])
                ->first();

            if (!$slugEntity) {
                // If no slug found, try to find the product directly (fallback)
                $product = $this->Products->find()
                    ->where(['slug' => $slug, 'is_published' => 1])
                    ->first();

                if (!$product) {
                    throw new NotFoundException(__('Product not found'));
                }

                $productId = $product->id;
            } else {
                $productId = $slugEntity->foreign_key;
            }

            // Check if it's the latest slug for the product
            $latestSlug = $this->Slugs->find()
                ->where(['foreign_key' => $productId])
                ->orderBy(['created' => 'DESC'])
                ->select(['slug'])
                ->first();

            // If $slug is not the same as the latestSlug, do a 301 redirect
            if ($latestSlug && $latestSlug->slug !== $slug) {
                return $this->redirect(
                    [
                        'controller' => 'Products',
                        'action' => 'view-by-slug',
                        'slug' => $latestSlug->slug,
                        '_full' => true,
                    ],
                    301,
                );
            }

            // Fetch the full product with its associations
            $product = $this->Products->find()
                ->where([
                    'Products.id' => $productId,
                    'Products.is_published' => 1,
                ])
                ->contain([
                    'Users',
                    'Tags',
                    'Comments' => function ($q) {
                        return $q->where(['Comments.display' => 1])
                                ->orderBy(['Comments.created' => 'DESC'])
                                ->contain(['Users']);
                    },
                    'Images',
                ])
                ->first();

            if (!$product) {
                throw new NotFoundException(__('Product not found'));
            }

            Cache::write($cacheKey, $product, 'content');
        }

        $this->viewBuilder()->setLayout($product->kind);

        $selectedTagId = false;

        // Get the child pages and breadcrumbs for the current product
        $childPages = $this->Products->find('children', for: $product->id)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . '_children', 'content')
            ->toArray();

        // Breadcrumbs
        $crumbs = $this->Products->find('path', for: $product->id)
            ->cache($cacheKey . '_crumbs', 'content')
            ->select(['slug', 'title', 'id'])
            ->all();

        $recentProducts = $this->Products->getRecentProducts($this->cacheKey, ['Products.id NOT IN' => [$product->id]]);

        $this->recordPageView($product->id);

        $this->set(compact(
            'product',
            'childPages',
            'selectedTagId',
            'crumbs',
            'recentProducts',
        ));

        return $this->render($product->kind);
    }

    /**
     * Adds a new comment to an product.
     *
     * @param string $productId The ID of the product to which the comment will be added.
     * @return \Cake\Http\Response|null
     */
    public function addComment(string $productId): ?Response
    {
        if (!$this->request->getSession()->read('Auth.id')) {
            $this->Flash->error(__('You must be logged in to add a comment.'));

            return $this->redirect($this->referer());
        }

        $product = $this->Products
            ->find()
            ->where(['id' => $productId])
            ->contain([])
            ->first();

        if (!$product) {
            $this->Flash->error(__('Product not found.'));

            return $this->redirect($this->referer());
        }

        if (
            (!SettingsManager::read('Comments.productsEnabled') && $product->kind == 'product')
            || (!SettingsManager::read('Comments.pagesEnabled') && $product->kind == 'page')
        ) {
            $this->Flash->error(__('Comments are not enabled'));

            return $this->redirect($this->referer());
        }

        $userId = $this->request->getSession()->read('Auth.id');
        $content = $this->request->getData('content');

        if ($this->Products->addComment($productId, $userId, $content)) {
            $this->Flash->success(__('Your comment has been added.'));
        } else {
            $this->Flash->error(__('Unable to add your comment.'));
        }

        return $this->redirect(Router::url([
            '_name' => 'product-by-slug',
            'slug' => $product->slug,
        ], true));
    }

    /**
     * Records a page view for a given product.
     *
     * @param string $productId The ID of the product being viewed
     * @return void
     */
    private function recordPageView(string $productId): void
    {
        $pageView = $this->PageViews->newEmptyEntity();
        $pageView->product_id = $productId;
        $pageView->ip_address = $this->request->clientIp();
        $pageView->user_agent = $this->request->getHeaderLine('User-Agent');
        $pageView->referer = $this->request->referer();
        $this->PageViews->save($pageView);
    }
}
