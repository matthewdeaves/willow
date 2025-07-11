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
 * Adapters Controller
 *
 * Manages adapter-related operations including viewing, listing, and commenting.
 *
 * @property \App\Model\Table\AdaptersTable $Adapters
 * @property \App\Model\Table\PageViewsTable $PageViews
 * @property \App\Model\Table\SlugsTable $Slugs
 */
class AdaptersController extends AppController
{
    /**
     * Default pagination configuration.
     *
     * Defines the default settings for paginating adapter records.
     * The limit determines how many adapters are displayed per page.
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
     * page view data, such as tracking adapter views and retrieving view statistics.
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
     * for an adapter, and managing slug history.
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
     * Retrieves and sets the root page adapter and a threaded list of all page adapters.
     *
     * @return void
     */
    public function pageIndex(): void
    {
        $adapter = $this->Adapters->find()
            ->orderBy(['lft' => 'ASC'])
            ->where([
                'Adapters.kind' => 'page',
                'Adapters.is_published' => 1,
            ])
            ->first();

        $adapters = $this->Adapters->getTree();

        $this->set(compact('adapter', 'adapters'));
    }

    /**
     * Displays a paginated list of published adapters.
     *
     * Retrieves published adapters with optional tag filtering.
     *
     * @return void
     */
    public function index(): void
    {
        $cacheKey = $this->cacheKey;
        $adapters = Cache::read($cacheKey, 'content');
        $selectedTagId = $this->request->getQuery('tag');

        if (!$adapters) {
            $query = $this->Adapters->find()
                ->where([
                    'Adapters.kind' => 'adapter',
                    'Adapters.is_published' => 1,
                ])
                ->contain(['Users', 'Tags'])
                ->orderBy(['Adapters.published' => 'DESC']);

            if ($selectedTagId) {
                $query->matching('Tags', function ($q) use ($selectedTagId) {
                    return $q->where(['Tags.id' => $selectedTagId]);
                });
            }

            $year = $this->request->getQuery('year');
            $month = $this->request->getQuery('month');

            if ($year) {
                $conditions = ['YEAR(Adapters.published)' => $year];
                if ($month) {
                    $conditions['MONTH(Adapters.published)'] = $month;
                }
                $query->where($conditions);
            }

            $adapters = $this->paginate($query);
            Cache::write($cacheKey, $adapters, 'content');
        }

        $recentAdapters = [];
        if ($this->request->getQuery('page') > 1) {
            $recentAdapters = $this->Adapters->getRecentAdapters($this->cacheKey);
        }

        $this->set(compact(
            'adapters',
            'selectedTagId',
            'recentAdapters',
        ));

        $this->viewBuilder()->setLayout('adapter_index');
    }

    /**
     * Displays an adapter by its slug.
     *
     * Retrieves an adapter using the provided slug, handling caching and redirects.
     *
     * @param string $slug The slug of the adapter to view.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\NotFoundException If the adapter is not found.
     */
    public function viewBySlug(string $slug): ?Response
    {
        $cacheKey = $slug . $this->cacheKey;
        $adapter = Cache::read($cacheKey, 'content');

        if (empty($adapter)) {
            // If not in cache, we need to check if this is the latest slug
            $slugEntity = $this->Slugs->find()
                ->where([
                    'slug' => $slug,
                    'model' => 'Adapters',
                    ])
                ->orderBy(['created' => 'DESC'])
                ->select(['foreign_key'])
                ->first();

            if (!$slugEntity) {
                // If no slug found, try to find the adapter directly (fallback)
                $adapter = $this->Adapters->find()
                    ->where(['slug' => $slug, 'is_published' => 1])
                    ->first();

                if (!$adapter) {
                    throw new NotFoundException(__('Adapter not found'));
                }

                $adapterId = $adapter->id;
            } else {
                $adapterId = $slugEntity->foreign_key;
            }

            // Check if it's the latest slug for the adapter
            $latestSlug = $this->Slugs->find()
                ->where(['foreign_key' => $adapterId])
                ->orderBy(['created' => 'DESC'])
                ->select(['slug'])
                ->first();

            // If $slug is not the same as the latestSlug, do a 301 redirect
            if ($latestSlug && $latestSlug->slug !== $slug) {
                return $this->redirect(
                    [
                        'controller' => 'Adapters',
                        'action' => 'view-by-slug',
                        'slug' => $latestSlug->slug,
                        '_full' => true,
                    ],
                    301,
                );
            }

            // Fetch the full adapter with its associations
            $adapter = $this->Adapters->find()
                ->where([
                    'Adapters.id' => $adapterId,
                    'Adapters.is_published' => 1,
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

            if (!$adapter) {
                throw new NotFoundException(__('Adapter not found'));
            }

            Cache::write($cacheKey, $adapter, 'content');
        }

        $this->viewBuilder()->setLayout($adapter->kind);

        $selectedTagId = false;

        // Get the child pages and breadcrumbs for the current adapter
        $childPages = $this->Adapters->find('children', for: $adapter->id)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . '_children', 'content')
            ->toArray();

        // Breadcrumbs
        $crumbs = $this->Adapters->find('path', for: $adapter->id)
            ->cache($cacheKey . '_crumbs', 'content')
            ->select(['slug', 'title', 'id'])
            ->all();

        $recentAdapters = $this->Adapters->getRecentAdapters($this->cacheKey, ['Adapters.id NOT IN' => [$adapter->id]]);

        $this->recordPageView($adapter->id);

        $this->set(compact(
            'adapter',
            'childPages',
            'selectedTagId',
            'crumbs',
            'recentAdapters',
        ));

        return $this->render($adapter->kind);
    }

    /**
     * Adds a new comment to an adapter.
     *
     * @param string $adapterId The ID of the adapter to which the comment will be added.
     * @return \Cake\Http\Response|null
     */
    public function addComment(string $adapterId): ?Response
    {
        if (!$this->request->getSession()->read('Auth.id')) {
            $this->Flash->error(__('You must be logged in to add a comment.'));

            return $this->redirect($this->referer());
        }

        $adapter = $this->Adapters
            ->find()
            ->where(['id' => $adapterId])
            ->contain([])
            ->first();

        if (!$adapter) {
            $this->Flash->error(__('Adapter not found.'));

            return $this->redirect($this->referer());
        }

        if (
            (!SettingsManager::read('Comments.adaptersEnabled') && $adapter->kind == 'adapter')
            || (!SettingsManager::read('Comments.pagesEnabled') && $adapter->kind == 'page')
        ) {
            $this->Flash->error(__('Comments are not enabled'));

            return $this->redirect($this->referer());
        }

        $userId = $this->request->getSession()->read('Auth.id');
        $content = $this->request->getData('content');

        if ($this->Adapters->addComment($adapterId, $userId, $content)) {
            $this->Flash->success(__('Your comment has been added.'));
        } else {
            $this->Flash->error(__('Unable to add your comment.'));
        }

        return $this->redirect(Router::url([
            '_name' => 'adapter-by-slug',
            'slug' => $adapter->slug,
        ], true));
    }

    /**
     * Records a page view for a given adapter.
     *
     * @param string $adapterId The ID of the adapter being viewed
     * @return void
     */
    private function recordPageView(string $adapterId): void
    {
        $pageView = $this->PageViews->newEmptyEntity();
        $pageView->adapter_id = $adapterId;
        $pageView->ip_address = $this->request->clientIp();
        $pageView->user_agent = $this->request->getHeaderLine('User-Agent');
        $pageView->referer = $this->request->referer();
        $this->PageViews->save($pageView);
    }
}
