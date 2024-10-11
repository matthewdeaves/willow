<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\PageViewsTable;
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
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        // Allow view, index, and viewBySlug actions to be accessed without authentication
        $this->Authentication->addUnauthenticatedActions(['view', 'index', 'viewBySlug', 'pageIndex']);

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
        $query = $this->Articles->find()
            ->where([
                'Articles.is_page' => 0,
                'Articles.is_published' => 1,
            ])
            ->contain(['Users'])
            ->orderBy(['Articles.published' => 'DESC']);
        $articles = $this->paginate($query);

        $this->set(compact('articles'));
    }

    /**
     * Displays an article by its slug.
     *
     * This method retrieves an article based on the provided slug,
     * loads its associated data (Users, Tags, and Comments),
     * records a page view, and sets the article data for the view.
     *
     * @param string $slug The unique slug of the article to retrieve.
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found.
     * @return void
     */
    public function viewBySlug(string $slug): void
    {
        $query = $this->Articles->find()
            ->where(['Articles.slug' => $slug]);

        $article = $query->first();

        if (!$article) {
            throw new NotFoundException(__('Article not found'));
        }

        $this->Articles->loadInto($article, [
            'Users',
            'Tags',
            'Comments' => function ($q) {
                return $q->where(['Comments.display' => 1])
                         ->order(['Comments.created' => 'DESC'])
                         ->contain(['Users']);
            },
        ]);

        // Record page view
        $this->recordPageView($article->id);

        $this->set(compact('article'));
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
