<?php
declare(strict_types=1);

namespace App\Controller\Admin\Trait;

use Cake\Http\Response;
use Cake\ORM\Query\SelectQuery;

/**
 * SearchableTrait
 *
 * Provides common search, pagination, and AJAX handling functionality
 * for admin controllers. This trait standardizes the index action pattern
 * used across admin controllers.
 *
 * Usage:
 *   class MyController extends AppController
 *   {
 *       use SearchableTrait;
 *
 *       public function index(): ?Response
 *       {
 *           $query = $this->MyModel->find();
 *           return $this->handleSearch(
 *               $query,
 *               'myEntities',
 *               ['title', 'description'] // searchable fields
 *           );
 *       }
 *   }
 */
trait SearchableTrait
{
    /**
     * Handle common search, pagination, and AJAX response pattern
     *
     * @param \Cake\ORM\Query\SelectQuery $query The base query
     * @param string $variableName The variable name to set for the view (e.g., 'articles')
     * @param array<string> $searchFields Fields to search in (e.g., ['title', 'body'])
     * @param string|null $tableName Table alias for field prefixing (auto-detected if null)
     * @param string $searchResultsTemplate Template name for AJAX results
     * @return \Cake\Http\Response|null
     */
    protected function handleSearch(
        SelectQuery $query,
        string $variableName,
        array $searchFields,
        ?string $tableName = null,
        string $searchResultsTemplate = 'search_results',
    ): ?Response {
        // Auto-detect table name from controller name if not provided
        if ($tableName === null) {
            $tableName = $this->getName();
        }

        // Apply search filter
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $orConditions = [];
            foreach ($searchFields as $field) {
                // If field already has table prefix, use as-is
                if (str_contains($field, '.')) {
                    $orConditions[$field . ' LIKE'] = '%' . $search . '%';
                } else {
                    $orConditions[$tableName . '.' . $field . ' LIKE'] = '%' . $search . '%';
                }
            }
            $query->where(['OR' => $orConditions]);
        }

        // Paginate results
        $results = $this->paginate($query);

        // Handle AJAX request
        if ($this->request->is('ajax')) {
            $this->set([$variableName => $results, 'search' => $search]);
            $this->viewBuilder()->setLayout('ajax');

            return $this->render($searchResultsTemplate);
        }

        // Normal request
        $this->set([$variableName => $results]);

        return null;
    }

    /**
     * Handle common search with status filter pattern
     *
     * @param \Cake\ORM\Query\SelectQuery $query The base query
     * @param string $variableName The variable name to set for the view
     * @param array<string> $searchFields Fields to search in
     * @param string $statusField The field name for status filtering
     * @param string|null $tableName Table alias for field prefixing
     * @param string $searchResultsTemplate Template name for AJAX results
     * @return \Cake\Http\Response|null
     */
    protected function handleSearchWithStatus(
        SelectQuery $query,
        string $variableName,
        array $searchFields,
        string $statusField = 'is_published',
        ?string $tableName = null,
        string $searchResultsTemplate = 'search_results',
    ): ?Response {
        // Auto-detect table name from controller name if not provided
        if ($tableName === null) {
            $tableName = $this->getName();
        }

        // Apply status filter
        $statusFilter = $this->request->getQuery('status');
        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where([$tableName . '.' . $statusField => (int)$statusFilter]);
        }

        return $this->handleSearch(
            $query,
            $variableName,
            $searchFields,
            $tableName,
            $searchResultsTemplate,
        );
    }
}
