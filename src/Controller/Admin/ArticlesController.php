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
 * Handles CRUD operations for articles, including pages and blog posts.
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    use ArticleCacheTrait;

    /**
     * Retrieves a hierarchical list of articles that are marked as pages.
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
     * @return \Cake\Http\Response|null The JSON response indicating success or failure.
     * @throws \Exception If an error occurs during the reordering process.
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
     * Displays a list of articles with search functionality.
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
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
                'Articles.summary',
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
            ->where(['Articles.kind' => 'article'])
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

        return null;
    }

    /**
     * Displays details of a specific article.
     *
     * @param string|null $id Article id.
     * @return void
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
                'Articles.summary',
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
     * Adds a new article.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['kind'] = $this->request->getQuery('kind', 'article');
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

                // Redirect to treeIndex if is page, otherwise to index
                if ($article->kind == 'page') {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }

        // Fetch parent articles if 'kind' is page
        $parentArticles = [];
        if ($this->request->getQuery('kind') == 'page') {
            $parentArticles = $this->Articles->find('list')
                ->where(['kind' => 'page'])
                ->all();
        }

        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $token = $this->request->getAttribute('csrfToken');
        $this->set(compact('article', 'users', 'tags', 'token', 'parentArticles'));

        return null;
    }

    /**
     * Edits an existing article.
     *
     * @param string|null $id Article ID.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $article = $this->Articles->get($id, contain: ['Tags', 'Images']);
        $oldSlug = $article->slug;
        $oldParent = $article->parent_id;

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $data['kind'] = $this->request->getQuery('kind', 'article');
            $article = $this->Articles->patchEntity($article, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads') ?? [];
            if (!empty($imageUploads['image_uploads'])) {
                $article->imageUploads = $imageUploads['image_uploads'];
            }

            // Handle image unlinking
            $unlinkedImages = $this->request->getData('unlink_images') ?? [];
            $article->unlinkedImages = $unlinkedImages;

            // Parent can't be child to itself
            if ($article->parent_id == $id) {
                $article->parent_id = $oldParent;
            }

            $saveOptions = [];
            if (isset($data['regenerateTags'])) {
                $saveOptions['regenerateTags'] = $data['regenerateTags'];
            }

            if ($this->Articles->save($article, $saveOptions)) {
                // Clear cache for both old and new slugs
                $this->clearFromCache($oldSlug);
                $this->clearFromCache($article->slug);

                $this->Flash->success(__('The article has been saved.'));

                // Redirect to treeIndex if kind is page, otherwise to index
                if ($article->kind == 'page') {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }

        // Fetch parent articles if 'kind' is page
        $parentArticles = [];
        if ($this->request->getQuery('kind') == 'page') {
            $parentArticles = $this->Articles->find('list')
                ->where(['kind' => 'page'])
                ->all();
        }

        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $this->set(compact('article', 'users', 'tags', 'parentArticles'));

        return null;
    }

    /**
     * Deletes an article.
     *
     * @param string|null $id Article ID.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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

        // Check if 'kind' is in the request parameters
        if ($this->request->getData('kind') == 'page') {
            return $this->redirect(['action' => 'treeIndex']);
        }

        return $this->redirect(['action' => 'index']);
    }
}
