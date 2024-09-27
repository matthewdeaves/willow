<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use App\Model\Table\PageViewsTable;
use Cake\Cache\Cache;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
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
     * Initialize method
     *
     * This method is called after the controller's constructor. It sets up
     * the controller by initializing properties and loading necessary components.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
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
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

        // Allow view, index, and viewBySlug actions to be accessed without authentication
        $this->Authentication->addUnauthenticatedActions(['view', 'index', 'viewBySlug', 'pageIndex']);
    }
    
    public function pageIndex()
    {
        // Get the first node in the tree (root node) that is a page
        $article = $this->Articles->find()
            ->orderBy(['lft' => 'ASC'])
            ->where(['Articles.is_page' => 1])
            ->first();
        
        $query = $this->Articles->find()
            ->select([
                'id',
                'parent_id',
                'title',
                'slug',
                'created',
                'modified',
                'Users.id',
                'Users.username',
            ])
            ->where([
                'Articles.is_page' => 1
            ])
            ->contain(['Users'])
            ->orderBy(['lft' => 'ASC']);
    
        $articles = $query->find('threaded')->toArray();
        
        $this->set(compact('article', 'articles'));
    }

    /**
     * Index method for fetching and displaying a paginated list of articles.
     *
     * This method attempts to retrieve a cached list of articles for the current page.
     * If the cache is not available, it queries the database for articles, including
     * associated user data, ordered by creation date in descending order. The result
     * is then paginated and stored in the cache for future requests.
     *
     * The paginated articles are set to the view for rendering.
     *
     * @return void
     */
    public function index()
    {
        $cacheKey = 'articles_index_page_' . $this->request->getQuery('page', 1);
        $articles = Cache::read($cacheKey, 'articles');

        if (!$articles) {
            $query = $this->Articles->find()
                ->contain(['Users'])
                ->orderBy(['Articles.created' => 'DESC']);
            $articles = $this->paginate($query);
            Cache::write($cacheKey, $articles, 'articles');
        }

        $this->set(compact('articles'));
    }

    /**
     * View an article by its slug.
     *
     * This method retrieves an article from the database using the provided slug.
     * It utilizes CakePHP's dynamic finder `findBySlug()` to query the article
     * and `firstOrFail()` to either fetch the first record or throw a
     * `\Cake\Datasource\Exception\RecordNotFoundException` if no record is found.
     * The retrieved article is then set to the view for rendering.
     *
     * @param string $slug The slug of the article to be viewed.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When no article is found for the given slug.
     */
    public function viewBySlug($slug)
    {
        // Attempt to retrieve the article from the cache
        $cacheKey = 'article_' . $slug;
        $article = Cache::read($cacheKey, 'articles');

        if (!$article) {
            $article = $this->Articles
                ->findBySlug($slug)
                ->contain(['Users', 'Tags', 'Comments'])
                ->firstOrFail();
            
            // Store the article in the cache
            Cache::write($cacheKey, $article, 'articles');
        }

        // Attempt to retrieve comments from the cache
        $commentsCacheKey = 'comments_article_' . $article->id;
        $comments = Cache::read($commentsCacheKey, 'articles');
        if (!$comments) {
            $comments = $this->Articles->getComments($article->id);
            // Store the comments in the cache
            Cache::write($commentsCacheKey, $comments, 'articles');
        }
        
        // Record page view
        $this->recordPageView($article->id);

        $this->set(compact('article', 'comments'));
    }

    /**
     * Adds a comment to an article.
     *
     * This method handles the addition of a comment to a specified article. It first checks if the user is logged in,
     * and if not, it redirects them back with an error message. It then verifies the existence of the article by its ID.
     * If the article is not found, it redirects back with an error message. If the article is found, it attempts to add
     * the comment using the provided article ID, user ID, and comment content. Upon successful addition, it clears
     * the cache for the article's comments and displays a success message. If the addition fails, it displays an error
     * message. Finally, it redirects the user to the article's view page.
     *
     * @param int $articleId The ID of the article to which the comment is to be added.
     * @return \Cake\Http\Response|null Redirects to the referring page or the article's view page.
     */
    public function addComment($articleId)
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
            $commentsCacheKey = 'comments_article_' . $article->id;
            // Clear the cache entry for the article's comments
            Cache::delete($commentsCacheKey, 'articles');

            $this->Flash->success(__('Your comment has been added.'));
        } else {
            $this->Flash->error(__('Unable to add your comment.'));
        }
    
        return $this->redirect(['action' => 'viewBySlug', $article->slug]);
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
    private function recordPageView($articleId)
    {
        $pageView = $this->PageViews->newEmptyEntity();
        $pageView->article_id = $articleId;
        $pageView->ip_address = $this->request->clientIp();
        $pageView->user_agent = $this->request->getHeaderLine('User-Agent');
        $pageView->referer = $this->request->referer();
        $this->PageViews->save($pageView);
    }
}
