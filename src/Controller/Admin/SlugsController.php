<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Slugs Controller
 *
 * @property \App\Model\Table\SlugsTable $Slugs
 */
class SlugsController extends AppController
{
    /**
     * Index method for retrieving and displaying slugs and associated articles.
     *
     * This method handles both standard and AJAX requests. For AJAX requests, it supports
     * searching through slugs and articles based on a search query. The results are rendered
     * using an AJAX-specific layout. For standard requests, it paginates the results and
     * renders them using the default layout.
     *
     * @return \Cake\Http\Response The response object containing the rendered view.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When no record is found.
     * @uses \Cake\ORM\Table::find() To create a query for retrieving slugs and associated articles.
     * @uses \Cake\ORM\Query::select() To specify the fields to be selected in the query.
     * @uses \Cake\ORM\Query::contain() To include associated articles in the query.
     * @uses \Cake\Http\ServerRequest::is() To check if the request is an AJAX request.
     * @uses \Cake\Http\ServerRequest::getQuery() To retrieve the search query from the request.
     * @uses \Cake\ORM\Query::where() To apply search conditions to the query.
     * @uses \Cake\ORM\Query::all() To execute the query and retrieve all matching records.
     * @uses \Cake\Controller\Controller::set() To pass data to the view.
     * @uses \Cake\View\ViewBuilder::setLayout() To set the layout for the view.
     * @uses \Cake\Controller\Controller::render() To render the view.
     * @uses \Cake\Controller\Component\PaginatorComponent::paginate() To paginate the query results.
     */
    public function index(): Response
    {
        $query = $this->Slugs->find()
            ->select([
                'Slugs.id',
                'Slugs.slug',
                'Slugs.article_id',
                'Slugs.created',
                'Slugs.modified',
                'Articles.id',
                'Articles.title',
                'Articles.slug',
            ])
            ->contain(['Articles'])
            ->orderBy(['Slugs.modified' => 'DESC']);

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'Slugs.slug LIKE' => '%' . $search . '%',
                        'Articles.title LIKE' => '%' . $search . '%',
                        'Articles.body LIKE' => '%' . $search . '%',
                        'Articles.slug LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $slugs = $query->all();
            $this->set(compact('slugs'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $slugs = $this->paginate($query);
        $this->set(compact('slugs'));

        return $this->render();
    }

    /**
     * View method
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): Response
    {
        $slug = $this->Slugs->get($id, contain: ['Articles']);
        $this->set(compact('slug'));

        return $this->render();
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): Response
    {
        $slug = $this->Slugs->newEmptyEntity();
        if ($this->request->is('post')) {
            $slug = $this->Slugs->patchEntity($slug, $this->request->getData());
            if ($this->Slugs->save($slug)) {
                $this->Flash->success(__('The slug has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The slug could not be saved. Please, try again.'));
        }
        $articles = $this->Slugs->Articles->find('list')->all();
        $this->set(compact('slug', 'articles'));

        return $this->render();
    }

    /**
     * Edit method
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): Response
    {
        $slug = $this->Slugs->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $slug = $this->Slugs->patchEntity($slug, $this->request->getData());
            if ($this->Slugs->save($slug)) {
                $this->Flash->success(__('The slug has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The slug could not be saved. Please, try again.'));
        }
        $articles = $this->Slugs->Articles->find('list', limit: 200)->all();
        $this->set(compact('slug', 'articles'));

        return $this->render();
    }

    /**
     * Delete method
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $slug = $this->Slugs->get($id);
        if ($this->Slugs->delete($slug)) {
            $this->Flash->success(__('The slug has been deleted.'));
        } else {
            $this->Flash->error(__('The slug could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
