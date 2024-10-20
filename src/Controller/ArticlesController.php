<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\PageViewsTable;
use App\Model\Table\SlugsTable;
use App\Model\Table\Trait\ArticleCacheTrait;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    use ArticleCacheTrait;

    /**
     * PageViews Table
     *
     * @var \App\Model\Table\PageViewsTable $PageViews
     *
     * This property holds an instance of the PageViewsTable class.
     * It is used to interact with the page_views table in the database.
     * The PageViewsTable provides methods for querying and manipulating
     * page view data, such as tracking article views and retrieving view statistics.
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
     * for an article, and managing slug history.
     */
    protected SlugsTable $Slugs;

    /**
     * Initializes the current table instance.
     *
     * This method is called after the constructor and is used to set up
     * associations, behaviors, and other initialization logic for the table.
     * It fetches the 'Slugs' and 'PageViews' tables and assigns them to
     * properties for later use.
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
     * Before filter callback.
     *
     * This method is called before the controller action is executed. It configures
     * certain actions to be accessible without requiring authentication.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        // Allow view, index, and viewBySlug actions to be accessed without authentication
        $this->Authentication->addUnauthenticatedActions(['view', 'index', 'viewBySlug', 'pageIndex']);

        if ($this->request->getParam('action') === 'addComment' && $this->request->is('post')) {
            // Check if the user is not logged in
            $result = $this->Authentication->getResult();
            if (!$result || !$result->isValid()) {
                // Save the request data to the session so once the user logs in their comment can be saved
                $session = $this->request->getSession();
                $session->write('Comment.formData', $this->request->getData());
            }
        }

        return null;
    }

    /**
     * Retrieves and sets page articles for the index view.
     *
     * This method performs two main operations:
     * 1. Fetches the first page article (root node) from the database.
     * 2. Retrieves a threaded list of all page articles with associated user information.
     *
     * The method orders articles by their left value ('lft') in ascending order,
     * ensuring a hierarchical structure. It then sets both the single article
     * and the threaded list of articles to the view context.
     *
     * @return void The method sets data to the view context but does not return a value.
     */
    public function pageIndex(): void
    {
        // Get the first node in the tree (root node) that is a page
        //todo do we need to get this first page?
        $article = $this->Articles->find()
            ->orderBy(['lft' => 'ASC'])
            ->where([
                'Articles.is_page' => 1,
                'Articles.is_published' => 1,
            ])
            ->first();

        $articles = $this->Articles->getPageTree();

        $this->set(compact('article', 'articles'));
    }

    /**
     * Displays a paginated list of published articles.
     *
     * This method retrieves all published articles that are not pages,
     * orders them by publication date in descending order,
     * includes associated user data, and paginates the results.
     * The paginated articles are then set for the view.
     *
     * @return void
     */
    public function index(): void
    {
        $selectedTag = $this->request->getQuery('tag');

        $query = $this->Articles->find()
            ->where([
                'Articles.is_page' => 0,
                'Articles.is_published' => 1,
            ])
            ->contain(['Users', 'Tags'])
            ->orderBy(['Articles.published' => 'DESC']);

        if ($selectedTag) {
            $query->matching('Tags', function ($q) use ($selectedTag) {
                return $q->where(['Tags.title' => $selectedTag]);
            });
        }

        $articles = $this->paginate($query);

        // Get all tags that have associated articles
        $tagsQuery = $this->Articles->Tags->find()
            ->matching('Articles', function ($q) {
                return $q->where([
                    'Articles.is_page' => 0,
                    'Articles.is_published' => 1,
                ]);
            })
            ->select(['Tags.title'])
            ->distinct(['Tags.title'])
            ->orderBy(['Tags.title' => 'ASC']);

        $tags = $tagsQuery->all()->extract('title')->toList();

        $this->set(compact('articles', 'tags', 'selectedTag'));
    }

    /**
     * View an article by its slug.
     *
     * This method attempts to retrieve an article using the provided slug. It first checks the cache for the article.
     * If not found in the cache, it verifies if the slug is the latest for the article. If the slug is outdated, it performs
     * a 301 redirect to the latest slug. The method fetches the full article with its associations if necessary and caches
     * it using the current slug. It also records a page view for analytics purposes.
     *
     * The method follows these steps:
     * 1. Check cache for the article.
     * 2. If not in cache, look up the slug in the database.
     * 3. If slug not found, attempt to find the article directly by slug.
     * 4. Verify if the slug is the latest for the article.
     * 5. If not the latest, perform a 301 redirect to the latest slug.
     * 6. Fetch the full article with associations.
     * 7. Cache the article.
     * 8. Record a page view.
     * 9. Set the article data for the view.
     *
     * @param string $slug The slug of the article to view.
     * @return \Cake\Http\Response|null Returns a Response object if a redirect is performed, otherwise null.
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found.
     */
    public function viewBySlug(string $slug): ?Response
    {
        // Try to get the article from cache first
        $article = $this->getFromCache($slug);

        if (empty($article)) {
            // If not in cache, we need to check if this is the latest slug
            $slugEntity = $this->Slugs->find()
                ->where(['slug' => $slug])
                ->orderBy(['created' => 'DESC'])
                ->select(['article_id'])
                ->first();

            if (!$slugEntity) {
                // If no slug found, try to find the article directly (fallback)
                $article = $this->Articles->find()
                    ->where(['slug' => $slug, 'is_published' => 1])
                    ->first();

                if (!$article) {
                    throw new NotFoundException(__('Article not found'));
                }

                $articleId = $article->id;
            } else {
                $articleId = $slugEntity->article_id;
            }

            // Check if it's the latest slug for the article
            $latestSlug = $this->Slugs->find()
                ->where(['article_id' => $articleId])
                ->orderBy(['created' => 'DESC'])
                ->select(['slug'])
                ->first();

            // If $slug is not the same as the latestSlug, do a 301 redirect
            if ($latestSlug && $latestSlug->slug !== $slug) {
                return $this->redirect('/' . $latestSlug->slug, 301);
            }

            // Fetch the full article with its associations
            $article = $this->Articles->find()
                ->where([
                    'Articles.id' => $articleId,
                    'Articles.is_published' => 1,
                ])
                ->contain([
                    'Users',
                    'Tags',
                    'Comments' => function ($q) {
                        return $q->where(['Comments.display' => 1])
                                ->order(['Comments.created' => 'DESC'])
                                ->contain(['Users']);
                    },
                    'Images',
                ])
                ->first();

            if (!$article) {
                throw new NotFoundException(__('Article not found'));
            }

            // Cache the article using the current (latest) slug
            $this->setToCache($article->slug, $article);
        }

        // Record page view
        $this->recordPageView($article->id);

        $this->set(compact('article'));

        return $this->render();
    }

    /**
     * Adds a new comment to an article.
     *
     * This method handles the process of adding a comment to a specific article.
     * It checks if the user is logged in, verifies the existence of the article,
     * and then attempts to add the comment using the provided data.
     *
     * @param string $articleId The ID of the article to which the comment will be added.
     * @return \Cake\Http\Response|null A response object for redirection, or null if the action doesn't redirect.
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found (implicitly through redirect).
     */
    public function addComment(string $articleId): ?Response
    {
        if (!$this->request->getSession()->read('Auth.id')) {
            $this->Flash->error(__('You must be logged in to add a comment.'));

            return $this->redirect($this->referer());
        }

        $article = $this->Articles
            ->find()
            ->where(['id' => $articleId])
            ->contain([])
            ->first();

        if (!$article) {
            $this->Flash->error(__('Article not found.'));

            return $this->redirect($this->referer());
        }

        $userId = $this->request->getSession()->read('Auth.id');
        $content = $this->request->getData('content');

        if ($this->Articles->addComment($articleId, $userId, $content)) {
            $this->Flash->success(__('Your comment has been added.'));
        } else {
            $this->Flash->error(__('Unable to add your comment.'));
        }

        return $this->redirect('/' . $article->slug);
    }

    /**
     * Records a page view for a given article.
     *
     * This private method creates a new PageView entity and populates it with:
     * - The ID of the viewed article
     * - The IP address of the client
     * - The User-Agent string from the request headers
     * - The referer URL
     *
     * After populating the entity, it saves it to the PageViews table.
     *
     * @param int $articleId The ID of the article being viewed
     * @return void
     */
    private function recordPageView(string $articleId): void
    {
        $pageView = $this->PageViews->newEmptyEntity();
        $pageView->article_id = $articleId;
        $pageView->ip_address = $this->request->clientIp();
        $pageView->user_agent = $this->request->getHeaderLine('User-Agent');
        $pageView->referer = $this->request->referer();
        $this->PageViews->save($pageView);
    }
}
