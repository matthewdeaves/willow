<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
// Keep this
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\JsonView;
use Exception;
// use Cake\Http\Exception\BadRequestException; // Not strictly needed if handling error codes directly

/**
 * Images Controller
 *
 * Manages CRUD operations for images and handles image selection for the Trumbowyg and Markdown-It editors.
 *
 * @property \App\Model\Table\ImagesTable $Images
 */
class ImagesController extends AppController
{
    /**
     * Specifies the view classes supported by this controller.
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
    public function imageSelect(): void
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
     * Handles bulk upload of images, showing the upload form for GET requests
     * and processing AJAX uploads for POST requests.
     *
     * @return \Cake\Http\Response|null Returns Response for AJAX requests, void for GET
     */
    public function bulkUpload(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        $response = $this->getResponse(); // Use getResponse() to get the current response object

        if ($this->request->is('get')) {
            // Render the bulk_upload.php template by default for GET
            return null;
        }

        // AJAX POST request handling
        // JsonView will be used automatically due to viewClasses() and Accept header

        $uploadedFile = $this->request->getUploadedFile('image');

        // Default error
        $apiResponse = [
            'success' => false,
            'message' => __('An unexpected error occurred.'),
        ];
        $statusCode = 500;

        if (!$uploadedFile) {
            $apiResponse['message'] = __('No file was uploaded or file key "image" is missing.');
            $statusCode = 400; // Bad Request
        } else {
            // Handle PHP Upload Errors (Suggestion 5)
            switch ($uploadedFile->getError()) {
                case UPLOAD_ERR_OK:
                    $image = $this->Images->newEmptyEntity();
                    $originalFilename = $uploadedFile->getClientFilename();
                    $data = [
                        'image' => $uploadedFile,
                        'name' => pathinfo($originalFilename, PATHINFO_FILENAME) ?: 'uploaded_image',
                    ];
                    $image = $this->Images->patchEntity($image, $data, ['validate' => 'create']);

                    if ($this->Images->save($image)) {
                        // Suggestion 8: Refined response structure
                        $apiResponse = [
                            'success' => true,
                            'message' => __('Image "{0}" uploaded successfully.', $image->name),
                            'image' => [
                                'id' => $image->id,
                                'name' => $image->name,
                                // Optionally, include a URL if useful for the client
                                // 'url' => \Cake\Routing\Router::url(['_full' => true, 'controller' => 'Images', 'action' => 'view', $image->id]),
                            ],
                        ];
                        $statusCode = 201; // Created
                    } else {
                        $apiResponse = [
                            'success' => false,
                            'message' => __('Failed to save the image. Please check errors.'),
                            'errors' => $image->getErrors(),
                        ];
                        $statusCode = 422; // Unprocessable Entity (Validation errors)
                    }
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $apiResponse['message'] = __('The uploaded file exceeds the maximum file size limit.');
                    $statusCode = 413; // Payload Too Large
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $apiResponse['message'] = __('The uploaded file was only partially uploaded.');
                    $statusCode = 400; // Bad Request (or 500 depending on interpretation)
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $apiResponse['message'] = __('No file was uploaded (UPLOAD_ERR_NO_FILE).');
                    $statusCode = 400; // Bad Request
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $apiResponse['message'] = __('Missing a temporary folder for upload.');
                    $statusCode = 500; // Internal Server Error
                    $this->log('Upload error: Missing temporary folder.', 'error');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $apiResponse['message'] = __('Failed to write file to disk.');
                    $statusCode = 500; // Internal Server Error
                    $this->log('Upload error: Failed to write file to disk.', 'error');
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $apiResponse['message'] = __('A PHP extension stopped the file upload.');
                    $statusCode = 500; // Internal Server Error
                    $this->log('Upload error: A PHP extension stopped the file upload.', 'error');
                    break;
                default:
                    $apiResponse['message'] = __('An unknown upload error occurred.');
                    $statusCode = 500; // Internal Server Error
                    $this->log('Unknown upload error code: ' . $uploadedFile->getError(), 'error');
                    break;
            }
        }

        // Suggestion 3: Use setOption('serialize', ...)
        $this->set($apiResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($apiResponse));

        return $response->withStatus($statusCode); // Suggestion 4: Return appropriate HTTP status
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

    /**
     * Deletes an image that was uploaded via bulk uploader.
     * Intended to be called by Dropzone's removedfile event.
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     * @throws \Cake\Http\Exception\MethodNotAllowedException If not a DELETE request.
     */
    public function deleteUploadedImage(?string $id = null): Response
    {
        $this->request->allowMethod(['delete']); // Suggestion 9: New action
        $response = $this->getResponse();

        $apiResponse = [
            'success' => false,
            'message' => __('Image could not be deleted.'),
        ];
        $statusCode = 500;

        if (!$id) {
            $apiResponse['message'] = __('No image ID provided.');
            $statusCode = 400;
        } else {
            try {
                $image = $this->Images->get($id);
                if ($this->Images->delete($image)) {
                    // Note: Ensure your ImagesTable->delete() or afterDelete event handles file system deletion.
                    $apiResponse = [
                        'success' => true,
                        'message' => __('Image "{0}" deleted successfully from server.', $image->name),
                    ];
                    $statusCode = 200; // OK
                } else {
                    $apiResponse['message'] = __('The image database record could not be deleted.');
                    // Errors might be on $image->getErrors() if the delete was blocked by rules.
                    if ($image->hasErrors()) {
                        $apiResponse['errors'] = $image->getErrors();
                        $statusCode = 422; // If rule-based delete failure
                    }
                }
            } catch (NotFoundException $e) {
                $apiResponse['message'] = __('Image not found.');
                $statusCode = 404; // Not Found
            } catch (Exception $e) {
                $this->log("Error_deleting_uploaded_image_{$id}: " . $e->getMessage(), 'error');
                $apiResponse['message'] = __('An unexpected error occurred while deleting the image.');
                $statusCode = 500;
            }
        }

        $this->set($apiResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($apiResponse));

        return $response->withStatus($statusCode);
    }
}
