<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Behavior;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Text;

/**
 * Slug Behavior
 *
 * This behavior automatically generates and manages URL-friendly slugs for model entities.
 * It maintains a history of slugs in a separate table and ensures slug uniqueness across the application.
 *
 * Features:
 * - Automatic slug generation from a specified source field
 * - Customizable maximum slug length
 * - Slug history tracking
 * - Uniqueness validation across current and historical slugs
 *
 * @property \Cake\ORM\Table $table The table this behavior is attached to
 */
class SlugBehavior extends Behavior
{
    use LocatorAwareTrait;

    /**
     * Default configuration for the behavior
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'sourceField' => 'title',
        'targetField' => 'slug',
        'maxLength' => 255,
    ];

    /**
     * Initialize the behavior
     *
     * Sets up the hasMany relationship to Slugs table and adds slug uniqueness validation.
     *
     * @param array<string, mixed> $config The configuration settings provided to this behavior
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Add the hasMany relationship to Slugs
        $this->_table->hasMany('Slugs', [
            'foreignKey' => 'foreign_key',
            'conditions' => ['Slugs.model' => $this->_table->getAlias()],
            'dependent' => true,
        ]);

        $this->_table->getValidator()->add($this->getConfig('targetField'), [
            'unique' => [
                'rule' => [$this, 'validateUniqueSlug'],
                'message' => __('This slug is already in use.'),
                'provider' => 'table',
            ],
        ]);
    }

    /**
     * Before save callback
     *
     * Generates and sets the slug on the entity before saving if necessary.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options passed to the save method
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $sourceField = $this->getConfig('sourceField');
        $targetField = $this->getConfig('targetField');
        $maxLength = $this->getConfig('maxLength');

        if (!empty($entity->get($targetField))) {
            // If a slug is provided, make it URL-safe
            $slug = $this->generateSlug($entity->get($targetField), $maxLength);
            $entity->set($targetField, $slug);
        } elseif (!empty($entity->get($sourceField))) {
            // If no slug but we have a source field, generate from source
            $slug = $this->generateSlug($entity->get($sourceField), $maxLength);
            $entity->set($targetField, $slug);
        }
    }

    /**
     * After save callback
     *
     * Records the slug in the slugs history table if it has changed.
     *
     * @param \Cake\Event\EventInterface $event The afterSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
     * @param \ArrayObject $options The options passed to the save method
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $targetField = $this->getConfig('targetField');
        $slug = $entity->get($targetField);

        if (empty($slug)) {
            return;
        }

        $slugsTable = $this->fetchTable('Slugs');

        // Check if the slug has actually changed
        $original = $entity->getOriginal($targetField);
        if (!$entity->isNew() && $original === $slug) {
            return;
        }

        // Check if this slug is already in the history for this entity
        $existingSlug = $slugsTable->find()
            ->select(['Slugs.id'])
            ->where([
                'Slugs.model' => $this->_table->getAlias(),
                'Slugs.foreign_key' => $entity->get($this->_table->getPrimaryKey()),
                'Slugs.slug' => $slug,
            ])
            ->first();

        if ($existingSlug === null) {
            $slugEntity = $slugsTable->newEntity([
                'model' => $this->_table->getAlias(),
                'foreign_key' => $entity->get($this->_table->getPrimaryKey()),
                'slug' => $slug,
                'created' => new DateTime(),
            ]);

            if (!$slugsTable->save($slugEntity)) {
                // Log the error instead of throwing an exception
                $errors = $slugEntity->getErrors();
                $errorMessages = [];
                foreach ($errors as $field => $fieldErrors) {
                    $errorMessages[] = sprintf('%s: %s', $field, implode(', ', $fieldErrors));
                }
                $event->getSubject()->log(sprintf(
                    'Failed to save slug history: %s',
                    implode('; ', $errorMessages)
                ));
            }
        }
    }

    /**
     * Generates a URL-safe slug from the given text
     *
     * @param string $text The text to convert into a slug
     * @param int $maxLength The maximum length for the generated slug
     * @return string The generated slug
     */
    protected function generateSlug(string $text, int $maxLength): string
    {
        $slug = Text::slug(strtolower($text), ['transliterator' => null]);

        return substr($slug, 0, $maxLength);
    }

    /**
     * Validates that a slug is unique across both the model table and slugs history
     *
     * @param mixed $value The slug value to check for uniqueness
     * @param array<string, mixed> $context The validation context including the current entity data
     * @return bool True if the slug is unique, false otherwise
     */
    public function validateUniqueSlug(mixed $value, array $context): bool
    {
        if (empty($value)) {
            return true;
        }

        $targetField = $this->getConfig('targetField');

        // Check uniqueness in the model's table using exists()
        $conditions = [$targetField => $value];
        if (!empty($context['data']['id'])) {
            $conditions['id !='] = $context['data']['id'];
        }

        if ($this->_table->exists($conditions)) {
            return false;
        }

        // Check uniqueness in the slugs table using exists()
        $slugsTable = $this->fetchTable('Slugs');
        $slugConditions = [
            'Slugs.slug' => $value,
            'Slugs.model' => $this->_table->getAlias(),
        ];

        if (!empty($context['data']['id'])) {
            $slugConditions[] = function (QueryExpression $exp) use ($context) {
                return $exp->notEq('Slugs.foreign_key', $context['data']['id']);
            };
        }

        return !$slugsTable->exists($slugConditions);
    }
}
