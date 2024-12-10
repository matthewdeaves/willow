<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\DateTime;
use RuntimeException;
use Cake\ORM\Locator\LocatorAwareTrait;

class SlugBehavior extends Behavior
{
    use LocatorAwareTrait;

    protected array $_defaultConfig = [
        'sourceField' => 'title',
        'targetField' => 'slug',
        'maxLength' => 255,
    ];

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

    public function beforeSave(EventInterface $event, EntityInterface $entity, \ArrayObject $options): void
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

    public function afterSave(EventInterface $event, EntityInterface $entity, \ArrayObject $options): void
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

    protected function generateSlug(string $text, int $maxLength): string
    {
        $slug = Text::slug(strtolower($text), ['transliterator' => null]);
        return substr($slug, 0, $maxLength);
    }

    public function validateUniqueSlug(mixed $value, array $context): bool
    {
        if (empty($value)) {
            return true;
        }

        $targetField = $this->getConfig('targetField');
        $query = $this->_table->find();
        
        // Check uniqueness in the model's table
        $conditions = [$targetField => $value];
        if (!empty($context['data']['id'])) {
            $conditions['id !='] = $context['data']['id'];
        }
        
        if ($query->where($conditions)->count() > 0) {
            return false;
        }

        // Check uniqueness in the slugs table
        $slugsTable = $this->fetchTable('Slugs');
        $slugQuery = $slugsTable->find();
        
        $slugConditions = [
            'Slugs.slug' => $value,
            'Slugs.model' => $this->_table->getAlias(),
        ];
        
        if (!empty($context['data']['id'])) {
            $slugConditions[] = function (QueryExpression $exp) use ($context) {
                return $exp->notEq('Slugs.foreign_key', $context['data']['id']);
            };
        }

        return $slugQuery->where($slugConditions)->count() === 0;
    }
}