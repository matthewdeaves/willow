<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Table\Trait\ArticleCacheTrait;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Exception;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    use ArticleCacheTrait;

    /**
     * Retrieves a hierarchical list of articles that are marked as pages.
     *
     * This method constructs a query to fetch articles from the database that are identified as pages
     * (i.e., where 'Articles.is_page' is set to 1). The query selects specific fields from the Articles
     * table, including 'id', 'parent_id', 'title', 'slug', 'created', and 'modified', as well as fields
     * from the associated Users table ('Users.id' and 'Users.username'). The results are ordered by the
     * 'lft' field in ascending order to maintain the hierarchical structure.
     *
     * The query uses the 'threaded' finder to organize the articles into a nested array structure,
     * reflecting their hierarchical relationships. The resulting array of articles is then set to the
     * view context for rendering.
     *
     * @return void
     */
    public function treeIndex(): void
    {
        $articles = $this->Articles->getPageTree();

        $this->set(compact('articles'));
    }

    /**
     * Updates the tree structure of articles.
     *
     * This method allows only POST and PUT HTTP methods. It retrieves the data from the request
     * and attempts to reorder the articles based on the provided data. The response is returned
     * in JSON format indicating the success or failure of the operation.
     *
     * @return \Cake\Http\Response The response object containing the JSON encoded result.
     * @throws \Exception If an error occurs during the reordering process, the exception message
     *                    is captured and returned in the response.
     */
    public function updateTree(): ?Response
    {
        $this->request->allowMethod(['post', 'put']);
        $data = $this->request->getData();

        try {
            $result = $this->Articles->reorder($data);

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'result' => $result]));
        } catch (Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Index method for fetching and displaying a list of articles.
     *
     * This method handles both standard and AJAX requests to display articles.
     * It constructs a query to select various fields from the Articles and Users tables,
     * including a count of page views for each article. The query filters out pages
     * (non-articles) and groups results by article and user identifiers.
     *
     * For AJAX requests, it supports searching articles by title, slug, body, and other
     * metadata fields. The search results are rendered using an 'ajax' layout.
     *
     * For standard requests, it paginates the articles and renders them using the default layout.
     *
     * @return \Cake\Http\Response The response object containing the rendered view.
     */
    public function index(): Response
    {
        $query = $this->Articles->find()
            ->select([
                'Articles.id',
                'Articles.user_id',
                'Articles.title',
                'Articles.slug',
                'Articles.image',
                'Articles.alt_text',
                'Articles.created',
                'Articles.modified',
                'Articles.published',
                'Articles.is_published',
                'Articles.body',
                'Articles.meta_title',
                'Articles.meta_description',
                'Articles.meta_keywords',
                'Articles.linkedin_description',
                'Articles.facebook_description',
                'Articles.instagram_description',
                'Articles.twitter_description',
                'Articles.word_count',
                'Users.id',
                'Users.username',
                'pageview_count' => $this->Articles->PageViews->find()
                    ->where(['PageViews.article_id = Articles.id'])
                    ->func()
                    ->count('PageViews.id'),
            ])
            ->leftJoinWith('Users')
            ->leftJoinWith('PageViews')
            ->where(['Articles.is_page' => false])
            ->groupBy([
                'Articles.id',
                'Articles.user_id',
                'Articles.title',
                'Articles.slug',
                'Articles.created',
                'Articles.modified',
                'Users.id',
                'Users.username',
            ])
            ->orderBy(['Articles.created' => 'DESC']);

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'Articles.title LIKE' => '%' . $search . '%',
                        'Articles.slug LIKE' => '%' . $search . '%',
                        'Articles.body LIKE' => '%' . $search . '%',
                        'Articles.meta_title LIKE' => '%' . $search . '%',
                        'Articles.meta_description LIKE' => '%' . $search . '%',
                        'Articles.meta_keywords LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $articles = $query->all();
            $this->set(compact('articles'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $articles = $this->paginate($query);
        $this->set(compact('articles'));

        return $this->render();
    }

    /**
     * View method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $article = $this->Articles->find()
            ->select([
                'Articles.id',
                'Articles.title',
                'Articles.slug',
                'Articles.body',
                'Articles.meta_title',
                'Articles.meta_description',
                'Articles.meta_keywords',
                'Articles.facebook_description',
                'Articles.linkedin_description',
                'Articles.twitter_description',
                'Articles.instagram_description',
                'Articles.word_count',
                'Articles.created',
                'Articles.modified',
                'Articles.published',
                'Articles.is_published',
                'Users.id',
                'Users.username',
                'Users.email',
            ])
            ->where(['Articles.id' => $id])
            ->contain(['Users', 'PageViews', 'Tags'])
            ->first();

        if (!$article) {
            throw new RecordNotFoundException(__('Article not found'));
        }

        $this->set(compact('article'));
    }

    /**
     * Add a new article.
     *
     * This method handles the creation of a new article. It processes POST requests,
     * sets the 'is_page' attribute based on query parameters, and attempts to save
     * the new article data. On successful save it redirects to the index or
     * treeIndex action. On failure, it displays an error message.
     *
     * The method also prepares data for the view, including:
     * - A list of parent articles (if 'is_page' query parameter is set)
     * - A list of users
     * - A list of tags
     * - CSRF token for form protection
     *
     * @return \Cake\Http\Response|null Redirects to index on successful save, null otherwise.
     */
    public function add(): Response
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['is_page'] = $this->request->getQuery('is_page', 0);
            $article = $this->Articles->patchEntity($article, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $article->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Articles->save($article)) {
                // Clear the cache for this new article
                $this->clearFromCache($article->slug);

                $this->Flash->success(__('The article has been saved.'));

                // Redirect to treeIndex if is_page is true, otherwise to index
                if ($article->is_page) {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }

        // Fetch parent articles if 'is_page' is set
        $parentArticles = [];
        if ($this->request->getQuery('is_page')) {
            $parentArticles = $this->Articles->find('list')
                ->where(['is_page' => true])
                ->all();
        }

        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $token = $this->request->getAttribute('csrfToken');
        $this->set(compact('article', 'users', 'tags', 'token', 'parentArticles'));

        return $this->render();
    }

    /**
     * Edit method for updating an existing article.
     *
     * This method handles the editing of an existing article. It performs the following operations:
     * - Retrieves the article by ID, including associated tags
     * - Processes PATCH, POST, or PUT requests
     * - Patches the article entity with submitted data
     * - Attempts to save the updated article
     * - Sets flash messages for success or failure
     * - Redirects to the index action on successful save
     *
     * It also prepares data for the view, including:
     * - Lists of users and tags for selection
     * - The article entity for form population
     *
     * @param string|null $id Article ID.
     * @return \Cake\Http\Response|null Redirects to index on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): Response
    {
        $article = $this->Articles->get($id, contain: ['Tags', 'Images']);
        $oldSlug = $article->slug;

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['is_page'] = $this->request->getQuery('is_page', 0);
            $article = $this->Articles->patchEntity($article, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads') ?? [];
            if (!empty($imageUploads['image_uploads'])) {
                $article->imageUploads = $imageUploads['image_uploads'];
            }

            // Handle image unlinking
            $unlinkedImages = $this->request->getData('unlink_images') ?? [];
            $article->unlinkedImages = $unlinkedImages;

            if ($this->Articles->save($article)) {
                // Clear cache for both old and new slugs
                $this->clearFromCache($oldSlug);
                $this->clearFromCache($article->slug);

                $this->Flash->success(__('The article has been saved.'));

                // Redirect to treeIndex if is_page is true, otherwise to index
                if ($article->is_page) {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }

        // Fetch parent articles if 'is_page' is set
        $parentArticles = [];
        if ($this->request->getQuery('is_page')) {
            $parentArticles = $this->Articles->find('list')
                ->where(['is_page' => true])
                ->all();
        }

        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $this->set(compact('article', 'users', 'tags', 'parentArticles'));

        return $this->render();
    }

    /**
     * Delete method
     *
     * This method handles the deletion of an article identified by its ID. It ensures that the request
     * method is either POST or DELETE to prevent accidental deletions via GET requests. Upon successful
     * deletion it displays a success message.
     * If the deletion fails, an error message is displayed. After the operation, the user is
     * redirected to the index action.
     *
     * @param string|null $id The ID of the article to be deleted. If null, no action is taken.
     * @return \Cake\Http\Response|null Redirects to the index action after attempting to delete the article.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When the article with the given ID does not exist.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $article = $this->Articles->get($id);
        if ($this->Articles->delete($article)) {
            // Clear the cache for this article
            $this->clearFromCache($article->slug);

            $this->Flash->success(__('The article has been deleted.'));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        // Check if 'is_page' is in the request parameters
        if ($this->request->getData('is_page')) {
            return $this->redirect(['action' => 'treeIndex']);
        }

        return $this->redirect(['action' => 'index']);
    }
}
