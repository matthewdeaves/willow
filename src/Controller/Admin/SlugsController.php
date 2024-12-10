<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Slugs Controller
 *
 * Handles administration of URL slugs across the application.
 * Provides CRUD operations for managing slugs and their relationships
 * with various content types (Articles, etc.).
 *
 * @property \App\Model\Table\SlugsTable $Slugs
 * @method \App\Model\Entity\Slug[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SlugsController extends AppController
{
    /**
     * Index method
     *
     * Lists all slugs with filtering and search capabilities.
     * Handles both regular and AJAX requests, displaying related content information
     * for each slug.
     *
     * Features:
     * - Search filtering by slug text
     * - Status filtering
     * - Related content association
     * - AJAX support for dynamic updates
     *
     * @return \Cake\Http\Response|null Returns Response for AJAX requests, null otherwise
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');

        // Get all unique model types from the slugs table
        $modelTypes = $this->Slugs->find()
            ->select(['model'])
            ->distinct('model')
            ->orderBy(['model' => 'ASC'])
            ->all()
            ->map(fn ($row) => ucfirst($row->model))
            ->toArray();

        $query = $this->Slugs->find()
            ->select([
                'Slugs.id',
                'Slugs.model',
                'Slugs.foreign_key',
                'Slugs.slug',
                'Slugs.created',
            ])
            ->orderBy(['Slugs.created' => 'DESC']);

        if (!empty($statusFilter)) {
            $query->where(['Slugs.model' => $statusFilter]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Slugs.slug LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        $slugs = $this->paginate($query);

        // Fetch related records for all slugs
        $relatedData = [];
        foreach ($slugs as $slug) {
            $relatedTable = $this->fetchTable($slug->model);

            // Build the select fields based on the model
            $selectFields = ['id', 'title'];
            if ($slug->model === 'Articles') {
                $selectFields[] = 'kind';
            }

            $relatedRecord = $relatedTable->find()
                ->select($selectFields)
                ->where(['id' => $slug->foreign_key])
                ->first();

            if ($relatedRecord) {
                $relatedData[$slug->id] = [
                    'title' => $relatedRecord->title,
                    'controller' => $slug->model,
                    'id' => $relatedRecord->id,
                ];

                // Add kind only for Articles
                if ($slug->model === 'Articles') {
                    $relatedData[$slug->id]['kind'] = $relatedRecord->kind;
                }
            }
        }

        if ($this->request->is('ajax')) {
            $this->set(compact('slugs', 'search', 'relatedData', 'modelTypes'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $this->set(compact('slugs', 'relatedData', 'modelTypes'));

        return null;
    }

    /**
     * View method
     *
     * Displays detailed information about a specific slug and its associated content.
     *
     * @param string|null $id The UUID of the slug to view
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When slug not found
     */
    public function view(?string $id = null): void
    {
        $slug = $this->Slugs->get($id, contain: ['Articles']);
        $this->set(compact('slug'));
    }

    /**
     * Add method
     *
     * Creates a new slug record with associated content relationship.
     * Provides form with content selection and slug input.
     *
     * @return \Cake\Http\Response|null|void Redirects to index on success, renders view otherwise
     */
    public function add(): ?Response
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
        $articles = $this->Slugs->Articles->find('list', limit: 200)->all();
        $this->set(compact('slug', 'articles'));

        return null;
    }

    /**
     * Edit method
     *
     * Modifies an existing slug record and its relationships.
     * Provides form with current values and content selection options.
     *
     * @param string|null $id The UUID of the slug to edit
     * @return \Cake\Http\Response|null|void Redirects to index on success, renders view otherwise
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When slug not found
     */
    public function edit(?string $id = null): ?Response
    {
        $slug = $this->Slugs->find()
            ->where(['id' => $id])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $slug = $this->Slugs->patchEntity($slug, $this->request->getData());
            if ($this->Slugs->save($slug)) {
                $this->Flash->success(__('The slug has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The slug could not be saved. Please, try again.'));
        }

        // Get related records based on the model type
        $relatedRecords = $this->fetchTable($slug->model)->find('list', limit: 200)->all();
        $this->set(compact('relatedRecords'));

        $this->set(compact('slug'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $slug = $this->Slugs->get($id);
        if ($this->Slugs->delete($slug)) {
            $this->Flash->success(__('The slug has been deleted.'));
        } else {
            $this->Flash->error(__('The slug could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
