<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\Utility\Text;
use Cake\Validation\Validator;

/**
 * SluggableBehavior
 *
 * This behavior automatically generates and manages slugs for entities in a CakePHP application.
 * It provides functionality to create unique, URL-safe slugs based on a specified field,
 * typically the title or name of the entity.
 *
 * Features:
 * - Automatic slug generation for new entities
 * - Customizable source field and destination slug field
 * - Configurable maximum slug length
 * - Ensures slug uniqueness within the table
 * - Provides validation rules for the slug field
 *
 * Configuration options:
 * - 'field': The field to base the slug on (default: 'title')
 * - 'slug': The field to store the slug (default: 'slug')
 * - 'maxLength': Maximum length of the slug (default: 255)
 *
 * @package App\Model\Behavior
 */
class SluggableBehavior extends Behavior
{
    protected array $_defaultConfig = [
        'field' => 'title', // The field to base the slug on
        'slug' => 'slug', // The field to store the slug
        'maxLength' => 255, // Maximum length of the slug
    ];

    /**
     * beforeSave callback.
     *
     * This method is triggered before an entity is saved. It generates a slug for new entities
     * if one is not provided, ensuring the slug is unique. If the generated slug is not unique,
     * it sets an error on the entity and prevents the save operation.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired.
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved.
     * @param \ArrayObject $options The options passed to the save method.
     * @return bool|null Returns false if the generated slug is not unique, preventing the save operation.
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): ?bool
    {
        $config = $this->getConfig();
        $field = $config['field'];
        $slugField = $config['slug'];

        if ($entity->isNew() && !$entity->get($slugField)) {
            $sluggedTitle = strtolower(Text::slug($entity->get($field)));
            // trim slug to maximum length defined in schema
            $entity->set($slugField, substr($sluggedTitle, 0, $config['maxLength']));

            //check generated slug is unique
            $existing = $this->table()->find()->where([$slugField => $entity->get($slugField)])->first();

            if ($existing) {
                // If not unique, set the slug back to the entity for user modification
                $entity->setError($slugField, 'The generated slug is not unique. Please modify it.');

                return false; // Prevent save
            }
        }

        return true;
    }

    /**
     * Builds the validator for the slug field.
     *
     * This method configures the validation rules for the slug field, including:
     * - Ensuring it's a scalar value
     * - Setting a maximum length of 255 characters
     * - Enforcing a URL-safe format using a regex pattern
     * - Requiring its presence on create operations
     * - Allowing empty strings
     * - Ensuring uniqueness across the table
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @param \Cake\Validation\Validator $validator The validator object.
     * @param string $name The name of the validator being built.
     * @return \Cake\Validation\Validator The modified validator object.
     */
    public function buildValidator(EventInterface $event, Validator $validator, string $name): Validator
    {
        $slugField = $this->getConfig('slug', 'slug');

        $validator
            ->scalar($slugField)
            ->maxLength($slugField, 255)
            ->regex(
                'slug',
                '/^[a-z0-9-]+$/',
                __('The slug must be URL-safe (only lowercase letters, numbers, and hyphens)')
            )
            ->allowEmptyString($slugField)
            ->add($slugField, 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => __('This slug is already in use. Please enter a unique slug.'),
            ]);

        return $validator;
    }
}
