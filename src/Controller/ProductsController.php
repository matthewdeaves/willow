<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Products Controller
 *
 * @property \App\Model\Table\ProductsTable $Products
 */
class ProductsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        /////TODO: SET CACHE FOR PRODUCTS AND MAKE VIEW FOR DEFAULT THEME
////////////////UNCOMMENT LATER //////
// $cacheKey = $this->cacheKey;
//         $articles = Cache::read($cacheKey, 'content');
//         $selectedTagId = $this->request->getQuery('tag');

        //         if (!$articles) {
//             $query = $this->Articles->find()
//                 ->where([
//                     'Articles.kind' => 'article',
//                     'Articles.is_published' => 1,
//                 ])
//                 ->contain(['Users', 'Tags'])
//                 ->orderBy(['Articles.published' => 'DESC']);

        //             if ($selectedTagId) {
//                 $query->matching('Tags', function ($q) use ($selectedTagId) {
//                     return $q->where(['Tags.id' => $selectedTagId]);
//                 });
//             }

        //             $year = $this->request->getQuery('year');
//             $month = $this->request->getQuery('month');

        //             if ($year) {
//                 $conditions = ['YEAR(Articles.published)' => $year];
//                 if ($month) {
//                     $conditions['MONTH(Articles.published)'] = $month;
//                 }
//                 $query->where($conditions);
//             }

        //             $articles = $this->paginate($query);
//             Cache::write($cacheKey, $articles, 'content');
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Products->find()
            ->contain(['Users', 'Articles']);


        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.slug LIKE' => '%' . $search . '%',
                    'Products.description LIKE' => '%' . $search . '%',
                    'Products.manufacturer LIKE' => '%' . $search . '%',
                    'Products.model_number LIKE' => '%' . $search . '%',
                    'Products.image LIKE' => '%' . $search . '%',
                    'Products.alt_text LIKE' => '%' . $search . '%',
                    'Products.verification_status LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $products = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('products', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('products'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $product = $this->Products->get($id, contain: ['Users', 'Articles', 'Tags', 'Slugs']);
        $this->set(compact('product'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $product = $this->Products->newEmptyEntity();
        if ($this->request->is('post')) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $users = $this->Products->Users->find('list', limit: 200)->all();
        $articles = $this->Products->Articles->find('list', limit: 200)->all();
        $tags = $this->Products->Tags->find('list', limit: 200)->all();
        $this->set(compact('product', 'users', 'articles', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $product = $this->Products->get($id, contain: ['Tags']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $users = $this->Products->Users->find('list', limit: 200)->all();
        $articles = $this->Products->Articles->find('list', limit: 200)->all();
        $tags = $this->Products->Tags->find('list', limit: 200)->all();
        $this->set(compact('product', 'users', 'articles', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $product = $this->Products->get($id);
        if ($this->Products->delete($product)) {
            $this->Flash->success(__('The product has been deleted.'));
        } else {
            $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
