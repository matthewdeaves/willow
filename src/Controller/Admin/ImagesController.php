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
     * Displays a list of images and handles AJAX search requests.
     * If it's an AJAX request, it filters images based on the search query.
     * Otherwise, it shows all images with pagination.
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): Response
    {
        $query = $this->Images->find();

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'name LIKE' => '%' . $search . '%',
                        'alt_text LIKE' => '%' . $search . '%',
                        'keywords LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $images = $query->all();
            $this->set(compact('images'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        // This will show all records by default
        $images = $this->paginate($query);
        $this->set(compact('images'));

        return $this->render();
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
     * Handles the selection of images for the Trumbowyg editor.
     *
     * This method sets up pagination for the images with a maximum limit of 8 per page.
     * It allows searching through images based on their name, alt text, or keywords.
     * The search query is retrieved from the request's query parameters.
     *
     * If a search term is provided, it filters the images accordingly.
     * The filtered images are then paginated and set to be available in the view.
     *
     * Additionally, it checks if only the gallery should be loaded based on the 'gallery_only'
     * query parameter. If true, it sets the template to 'image_gallery' and uses a minimal layout.
     * Otherwise, it uses a minimal layout without changing the template.
     *
     * @return void
     */
    public function trumbowygSelect(): void
    {
        $this->paginate = [
            'maxLimit' => 8,
        ];
        $query = $this->Images->find();
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'name LIKE' => '%' . $search . '%',
                    'alt_text LIKE' => '%' . $search . '%',
                    'keywords LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $images = $this->paginate($query);
        $this->set(compact('images'));

        // Check if we're loading just the gallery
        $loadGalleryOnly = $this->request->getQuery('gallery_only', false);
        if ($loadGalleryOnly) {
            $this->viewBuilder()->setTemplate('image_gallery');
            $this->viewBuilder()->setLayout('minimal');
        } else {
            $this->viewBuilder()->setLayout('minimal');
        }
    }

    /**
     * Adds a new image.
     *
     * This method handles the creation of a new image entity. It uses the 'create'
     * validation ruleset when processing the submitted form data. On successful save,
     * it redirects to the index action. If the save fails, it displays an error message.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): Response
    {
        $image = $this->Images->newEmptyEntity();
        if ($this->request->is('post')) {
            // Use validationCreate explicitly
            $image = $this->Images->patchEntity($image, $this->request->getData(), ['validate' => 'create']);
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
     * Handles bulk upload of images via AJAX request.
     *
     * This method processes an AJAX file upload, creates a new image entity,
     * and saves it to the database. It uses the 'create' validation ruleset
     * and sets the image name to the original filename (without extension).
     *
     * @return \Cake\Http\Response|null Returns a JSON response on AJAX requests,
     *                                  or renders the default view otherwise.
     */
    public function bulkUpload(): Response
    {
        if ($this->request->is('ajax')) {
            $uploadedFile = $this->request->getUploadedFile('file');
            if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
                $image = $this->Images->newEmptyEntity();

                // Get the original filename
                $originalFilename = $uploadedFile->getClientFilename();

                // Pass the UploadedFile object directly
                $data = [
                    'image_file' => $uploadedFile,
                    'name' => pathinfo($originalFilename, PATHINFO_FILENAME),
                ];

                // Use validationCreate explicitly
                $image = $this->Images->patchEntity($image, $data, ['validate' => 'create']);

                if ($this->Images->save($image)) {
                    return $this->response->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => true,
                            'message' => __('Image uploaded successfully'),
                        ]));
                } else {
                    return $this->response->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => __('Failed to save the image'),
                            'errors' => $image->getErrors(),
                        ]));
                }
            }
        }

        return $this->render();
    }

    /**
     * Edit method
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): Response
    {
        $image = $this->Images->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            // Use validationUpdate explicitly
            $image = $this->Images->patchEntity($image, $this->request->getData(), ['validate' => 'update']);
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
