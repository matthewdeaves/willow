<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;
use Cake\View\JsonView;

/**
 * Images Controller
 *
 * Manages CRUD operations for images and handles image selection for the Trumbowyg editor.
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
     * Lists images with support for standard and AJAX requests.
     *
     * @return \Cake\Http\Response The response object containing the rendered view.
     */
    public function index(): Response
    {
        $session = $this->request->getSession();
        $viewType = $this->request->getQuery('view');

        // Check if view type is provided in the query, otherwise use session value or default to 'list'
        if ($viewType) {
            $session->write('Images.viewType', $viewType);
        } else {
            $viewType = $session->read('Images.viewType', 'grid');
        }

        $query = $this->Images->find()
            ->select([
                'Images.id',
                'Images.name',
                'Images.image',
                'Images.dir',
                'Images.alt_text',
                'Images.keywords',
                'Images.created',
                'Images.modified',
            ]);

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
        if ($this->request->is('ajax')) {
            $this->set(compact('images', 'viewType', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('images', 'viewType'));

        return $this->render($viewType === 'grid' ? 'index_grid' : 'index');
    }

    /**
     * Displays details of a specific image.
     *
     * @param string|null $id Image id.
     * @return void
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
            'maxLimit' => 6,
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
    public function add(): ?Response
    {
        $image = $this->Images->newEmptyEntity();
        if ($this->request->is('post')) {
            $image = $this->Images->patchEntity($image, $this->request->getData(), ['validate' => 'create']);
            if ($this->Images->save($image)) {
                $this->Flash->success(__('The image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image could not be saved. Please, try again.'));
        }
        $this->set(compact('image'));

        return null;
    }

    /**
     * Handles bulk upload of images via AJAX request.
     *
     * This method processes an AJAX file upload, creates a new image entity,
     * and saves it to the database. It uses the 'create' validation ruleset
     * and sets the image name to the original filename (without extension).
     *
     * * @return \Cake\Http\Response|null JSON response for AJAX requests, null otherwise.
     */
    public function bulkUpload(): ?Response
    {
        if ($this->request->is('ajax')) {
            $uploadedFile = $this->request->getUploadedFile('image');
            if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
                $image = $this->Images->newEmptyEntity();
                $originalFilename = $uploadedFile->getClientFilename();
                $data = [
                    'image' => $uploadedFile,
                    'name' => pathinfo($originalFilename, PATHINFO_FILENAME),
                ];
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

        return null;
    }

    /**
     * Edits an existing image.
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $image = $this->Images->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $image = $this->Images->patchEntity($image, $this->request->getData(), ['validate' => 'update']);
            if ($this->Images->save($image)) {
                $this->Flash->success(__('The image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image could not be saved. Please, try again.'));
        }
        $this->set(compact('image'));

        return null;
    }

    /**
     * Deletes an image.
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response Redirects to index.
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

        return $this->redirect($this->referer(['action' => 'index']));
    }
}
