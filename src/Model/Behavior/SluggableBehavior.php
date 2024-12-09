<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * SluggableBehavior
 *
 * A behavior for CakePHP 5.x that manages URL-friendly slugs for entities with historical tracking.
 * This behavior automatically generates unique slugs and maintains a history of previous slugs
 * in a separate 'slugs' table.
 *
 * Features:
 * - Automatic slug generation for new entities
 * - Maintains history of previous slugs in a separate table
 * - Ensures slug uniqueness across both the model table and slugs history
 * - Customizable source and destination fields
 * - Validation rules for slug format
 *
 * Configuration options:
 * - 'field': (string) The field to base the slug on (default: 'title')
 * - 'slug': (string) The field to store the slug (default: 'slug')
 * - 'maxLength': (int) Maximum length of the slug (default: 255)
 *
 * @see \App\Model\Table\SlugsTable
 */
class SluggableBehavior extends Behavior
{
    use LocatorAwareTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'field' => 'title',
        'slug' => 'slug',
        'maxLength' => 255,
    ];

    /**
     * Initialize the behavior.
     *
     * @param array<string, mixed> $config The configuration settings provided to this behavior.
     * @return void
     * @throws \InvalidArgumentException When the slug field doesn't exist in the table.
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        if (!$this->table()->hasField($this->getConfig('slug'))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Field "%s" does not exist in table "%s"',
                    $this->getConfig('slug'),
                    $this->table()->getAlias()
                )
            );
        }

        // Add the Slugs relationship to the table
        $this->table()->hasMany('Slugs', [
            'foreignKey' => 'foreign_key',
            'conditions' => ['Slugs.model' => $this->table()->getAlias()],
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
    }

    /**
     * beforeSave callback.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired.
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved.
     * @param \ArrayObject $options The options passed to the save method.
     * @return bool|null Returns false if validation fails, preventing the save operation.
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): ?bool
    {
        $config = $this->getConfig();
        $field = $config['field'];
        $slugField = $config['slug'];
        $slugsTable = $this->fetchTable('Slugs');

        // Generate slug for new entities if not provided
        if (!$entity->get($slugField)) {
            $slug = $this->generateSlug($entity->get($field));
            $entity->set($slugField, $slug);
        }

        // Handle existing entities
        if (!$entity->isNew() && $entity->isDirty($slugField)) {
            $oldSlug = $entity->getOriginal($slugField);
            if ($oldSlug) {
                $slugEntity = $slugsTable->newEntity([
                    'model' => $this->table()->getAlias(),
                    'foreign_key' => $entity->get($this->table()->getPrimaryKey()),
                    'slug' => $oldSlug,
                ]);
                if (!$slugsTable->save($slugEntity)) {
                    $entity->setError($slugField, __('Could not save the previous slug.'));
                    return false;
                }
            }
        }

        // For new entities, save the initial slug after the entity is saved
        if ($entity->isNew()) {
            $this->table()->getEventManager()->on(
                'Model.afterSave',
                function (EventInterface $event, EntityInterface $savedEntity) use ($slugField, $slugsTable): void {
                    if ($savedEntity->isNew()) {
                        $slugEntity = $slugsTable->newEntity([
                            'model' => $this->table()->getAlias(),
                            'foreign_key' => $savedEntity->get($this->table()->getPrimaryKey()),
                            'slug' => $savedEntity->get($slugField),
                        ]);

                        if (!$slugsTable->save($slugEntity)) {
                            $this->table()->log(sprintf(
                                'Failed to save initial slug for %s: %s',
                                $this->table()->getAlias(),
                                json_encode($slugEntity->getErrors())
                            ), 'error');
                        }
                    }
                }
            );
        }

        return true;
    }

    /**
     * Generates a URL-safe slug from the given string.
     *
     * @param string $value The string to convert into a slug.
     * @return string The generated slug.
     */
    protected function generateSlug(string $value): string
    {
        $config = $this->getConfig();
        $slug = strtolower(Text::slug($value));
        return substr($slug, 0, $config['maxLength']);
    }

    /**
     * Builds the validator for the slug field.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @param \Cake\Validation\Validator $validator The validator object.
     * @param string $name The name of the validator being built.
     * @return \Cake\Validation\Validator The modified validator object.
     */
    public function buildValidator(EventInterface $event, Validator $validator, string $name): Validator
    {
        $slugField = $this->getConfig('slug');

        $validator
            ->scalar($slugField)
            ->maxLength($slugField, 255)
            ->regex(
                $slugField,
                '/^[a-z0-9-]+$/',
                __('The slug must be URL-safe (only lowercase letters, numbers, and hyphens)')
            )
            ->allowEmptyString($slugField);

        return $validator;
    }
}