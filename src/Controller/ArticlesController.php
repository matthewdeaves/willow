<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    /**
     * beforeFilter callback.
     *
     * Allow unauthenticated access to public articles
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to index, view, and viewBySlug actions
        $this->Authentication->addUnauthenticatedActions(['index', 'view', 'viewBySlug']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Articles->find()
            ->contain(['Users']);
        $articles = $this->paginate($query);

        $this->set(compact('articles'));
    }

    /**
     * View method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $article = $this->Articles->get($id, contain: ['Users', 'Images', 'Tags', 'Comments', 'Slugs', 'ArticlesTranslations', 'PageViews']);
        $this->set(compact('article'));
        // Use the article template for DefaultTheme
        $this->render('article');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('The article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }
        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $images = $this->Articles->Images->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $this->set(compact('article', 'users', 'images', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $article = $this->Articles->get($id, contain: ['Images', 'Tags']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('The article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }
        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $images = $this->Articles->Images->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $this->set(compact('article', 'users', 'images', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $article = $this->Articles->get($id);
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The article has been deleted.'));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * View by slug method
     *
     * @param string|null $slug Article slug.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function viewBySlug(?string $slug = null)
    {
        if (!$slug) {
            throw new NotFoundException(__('Article not found.'));
        }

        $article = $this->Articles->find()
            ->contain(['Users', 'Images', 'Tags', 'Comments', 'Slugs', 'ArticlesTranslations', 'PageViews'])
            ->where(['Articles.slug' => $slug])
            ->first();

        if (!$article) {
            throw new NotFoundException(__('Article not found.'));
        }

        $this->set(compact('article'));
        // Use the article template for DefaultTheme
        $this->render('article');
    }

    /**
     * Add comment method
     *
     * @return \Cake\Http\Response|null|void Redirects after posting comment
     */
    public function addComment()
    {
        $this->request->allowMethod(['post']);

        $articleId = $this->request->getData('article_id');
        if (!$articleId) {
            $this->Flash->error(__('Invalid article specified.'));

            return $this->redirect(['action' => 'index']);
        }

        // Check if article exists
        $article = $this->Articles->find()->where(['id' => $articleId])->first();
        if (!$article) {
            $this->Flash->error(__('Article not found.'));

            return $this->redirect(['action' => 'index']);
        }

        // Load Comments model and create comment
        $this->loadModel('Comments');
        $comment = $this->Comments->newEmptyEntity();
        $comment = $this->Comments->patchEntity($comment, $this->request->getData());
        $comment->article_id = $articleId;

        if ($this->Comments->save($comment)) {
            $this->Flash->success(__('Your comment has been posted.'));
        } else {
            $this->Flash->error(__('Unable to post your comment. Please, try again.'));
        }

        // Redirect back to the article
        if ($article->slug) {
            return $this->redirect(['action' => 'viewBySlug', $article->slug]);
        } else {
            return $this->redirect(['action' => 'view', $articleId]);
        }
    }
}
