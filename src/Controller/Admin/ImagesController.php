<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\View\JsonView;

/**
 * Images Controller
 *
 * @property \App\Model\Table\ImagesTable $Images
 */
class ImagesController extends AppController
{
    /**
     * Specifies the view classes supported by this controller.
     *
     * This method is used for content negotiation. It indicates that
     * this controller can render JSON responses using the JsonView class.
     *
     * @return array An array containing the fully qualified class name of JsonView.
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Executes before the controller action is called.
     *
     * This method intercepts the request for the 'trumbowygAdd' action.
     * It modifies the request data by renaming the 'alt' field to 'name'
     * for image uploads through the Trumbowyg editor.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return void
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        if ($this->request->getParam('action') == 'trumbowygAdd') {
            $postData = $this->request->getData();
            $postData['name'] = $postData['alt'];
            unset($postData['alt']);
            $this->request = $this->request->withParsedBody($postData);
        }

        return null;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): void
    {
        $query = $this->Images->find();
        $images = $this->paginate($query);

        $this->set(compact('images'));
    }

    /**
     * View method
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $image = $this->Images->get($id, contain: []);
        $this->set(compact('image'));
    }

    /**
     * trumbowygAdd method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function trumbowygAdd(): void
    {
        $image = $this->Images->newEmptyEntity();
        if ($this->request->is('post')) {
            $image = $this->Images->patchEntity($image, $this->request->getData());
            $savedImage = $this->Images->save($image);
            if ($savedImage) {
                $img = ['success' => true, 'file' => 'http://localhost:8765/files/Images/path/' . $savedImage->path];
                $this->set('img', $img);
                $this->viewBuilder()
                    ->setClassName('Json');
            }
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function trumbowygSelect(): void
    {
        $query = $this->Images->find();
        $images = $this->paginate($query);
        $this->set(compact('images'));
        $this->viewBuilder()->setLayout('minimal');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): Response
    {
        $image = $this->Images->newEmptyEntity();
        if ($this->request->is('post')) {
            $image = $this->Images->patchEntity($image, $this->request->getData());
            if ($this->Images->save($image)) {
                $this->Flash->success(__('The image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image could not be saved. Please, try again.'));
        }
        $this->set(compact('image'));

        return $this->render();
    }

    /**
     * Edit method
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): Response
    {
        $image = $this->Images->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $image = $this->Images->patchEntity($image, $this->request->getData());
            if ($this->Images->save($image)) {
                $this->Flash->success(__('The image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image could not be saved. Please, try again.'));
        }
        $this->set(compact('image'));

        return $this->render();
    }

    /**
     * Delete method
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $image = $this->Images->get($id);
        if ($this->Images->delete($image)) {
            $this->Flash->success(__('The image has been deleted.'));
        } else {
            $this->Flash->error(__('The image could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
