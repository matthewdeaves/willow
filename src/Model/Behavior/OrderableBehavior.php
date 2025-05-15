<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\ORM\Behavior;
use InvalidArgumentException;

/**
 * OrderableBehavior provides hierarchical ordering capabilities for CakePHP models.
 *
 * This behavior automatically includes and configures the Tree behavior, providing
 * methods to manage hierarchical data structures with ordering capabilities.
 * It allows for:
 * - Moving items between different levels of hierarchy
 * - Reordering items within the same level
 * - Root level and nested level ordering
 *
 * Required table columns:
 * - parent_id (integer|null): References the parent record
 * - lft (integer): Left value for nested set model
 * - rght (integer): Right value for nested set model
 *
 * @property \Cake\ORM\Table $_table The table instance this behavior is attached to
 */
class OrderableBehavior extends Behavior
{
    /**
     * Default configuration for the behavior.
     *
     * Configuration options:
     * - treeConfig: Configuration array for the Tree behavior
     *   - parent: The foreign key column for the parent (default: 'parent_id')
     *   - left: The column for the left value (default: 'lft')
     *   - right: The column for the right value (default: 'rght')
     * - displayField: The field to use for display in the tree (default: null, uses table's displayField)
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'treeConfig' => [
            'parent' => 'parent_id',
            'left' => 'lft',
            'right' => 'rght',
        ],
        'displayField' => null,
    ];

    /**
     * Initializes the behavior.
     *
     * Sets up the Tree behavior with merged configuration settings if it's not
     * already present on the table. This ensures all necessary tree functionality
     * is available for ordering operations.
     *
     * @param array $config Configuration array with optional 'treeConfig' key for
     *                      customizing the Tree behavior settings.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Merge any provided tree configuration with defaults
        $treeConfig = array_merge(
            $this->_defaultConfig['treeConfig'],
            $config['treeConfig'] ?? [],
        );

        // Add the Tree behavior if it's not already added
        if (!$this->_table->hasBehavior('Tree')) {
            $this->_table->addBehavior('Tree', $treeConfig);
        }
    }

    /**
     * Reorders a model record within a hierarchical structure.
     *
     * This method handles both the parent reassignment and sibling reordering
     * in a single operation. It supports:
     * - Moving an item to the root level
     * - Moving an item under a new parent
     * - Reordering items within their current level
     *
     * The method ensures proper tree structure maintenance through the Tree behavior
     * and updates the nested set values accordingly.
     *
     * @param array $data An associative array containing:
     *                    - 'id' (int): The ID of the record to be reordered.
     *                    - 'newParentId' (mixed): The ID of the new parent record, or 'root' to move to the root level.
     *                    - 'newIndex' (int): The new position index among siblings (zero-based).
     * @throws \InvalidArgumentException If the provided data is not an array or is missing required keys.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If the record cannot be saved.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If the record to move or target parent is not found.
     * @return bool Returns true on successful reordering.
     */
    public function reorder(array $data): bool
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        $model = $this->_table->get($data['id']);

        if ($data['newParentId'] === 'root') {
            // Moving to root level
            $model->parent_id = null;
            $this->_table->save($model);
        } else {
            // Moving to a new parent
            $newParent = $this->_table->get($data['newParentId']);
            $model->parent_id = $newParent->id;
            $this->_table->save($model);
        }

        // Adjust the position within siblings
        if ($model->parent_id === null) {
            // For root level items
            $siblings = $this->_table->find()
                ->where(['parent_id IS' => null])
                ->orderBy(['lft' => 'ASC'])
                ->toArray();
        } else {
            // For non-root items
            $siblings = $this->_table->find('children', for: $model->parent_id, direct: true)
                ->orderBy(['lft' => 'ASC'])
                ->toArray();
        }

        $currentPosition = array_search($model->id, array_column($siblings, 'id'));
        $newPosition = $data['newIndex'];

        if ($currentPosition !== false && $currentPosition !== $newPosition) {
            if ($newPosition > $currentPosition) {
                $this->_table->moveDown($model, $newPosition - $currentPosition);
            } else {
                $this->_table->moveUp($model, $currentPosition - $newPosition);
            }
        }

        return true;
    }

    /**
     * Gets a hierarchical tree structure of records.
     *
     * Retrieves records in a threaded format, including essential fields for tree structure
     * and any additional conditions specified.
     *
     * @param array $additionalConditions Additional conditions to apply to the query
     * @param array $fields Additional fields to select (beyond id, parent_id, and displayField)
     * @return array<\Cake\Datasource\EntityInterface> Array of entities in threaded format
     */
    public function getTree(array $additionalConditions = [], array $fields = []): array
    {
        $displayField = $this->getConfig('displayField') ?? $this->_table->getDisplayField();

        // Base fields that are always needed for tree structure
        $baseFields = [
            'id',
            'parent_id',
            $displayField,
        ];

        // Merge base fields with any additional fields
        $selectFields = array_unique(array_merge($baseFields, $fields));

        $query = $this->_table->find()
            ->select($selectFields)
            ->where($additionalConditions)
            ->orderBy(['lft' => 'ASC']);

        return $query->find('threaded')->toArray();
    }
}
