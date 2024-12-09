<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Slugs Model
 *
 * This model represents the slugs table and handles operations related to polymorphic slugs.
 * It dynamically creates relationships with models based on the 'model' field and implements
 * caching for improved performance.
 *
 * The slugs table structure:
 * - id (char(36)) - Primary key
 * - model (varchar(20)) - The model name
 * - foreign_key (char(36)) - The related model's primary key
 * - slug (varchar(255)) - The URL-friendly slug
 * - created (timestamp) - Creation timestamp
 *
 * @method \App\Model\Entity\Slug newEmptyEntity()
 * @method \App\Model\Entity\Slug newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Slug> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Slug get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Slug findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Slug patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Slug> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Slug|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Slug saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Slug>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Slug>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Slug>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Slug> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Slug>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Slug>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Slug>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Slug> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SlugsTable extends Table
{
    use LogTrait;

    /**
     * Cache configuration name for slugs
     *
     * @var string
     */
    public const CACHE_CONFIG = 'slugs';

    /**
     * Cache key for models list
     *
     * @var string
     */
    public const CACHE_MODELS_KEY = 'slugs_models';

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('slugs');
        $this->setDisplayField('slug');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Set up dynamic associations based on existing slugs
        $this->setupAssociations();
    }

    /**
     * Sets up dynamic associations based on the unique model values in the slugs table.
     * Uses cache to improve performance.
     *
     * @return void
     */
    protected function setupAssociations(): void
    {
        $models = $this->find()
            ->select(['model'])
            ->distinct(['model'])
            ->disableHydration()
            ->cache(self::CACHE_MODELS_KEY, self::CACHE_CONFIG)
            ->all()
            ->extract('model')
            ->toArray();

        foreach ($models as $model) {
            try {
                $className = App::className($model, 'Model/Table', 'Table');
                if ($className) {
                    $this->belongsTo($model, [
                        'foreignKey' => 'foreign_key',
                        'conditions' => ['Slugs.model' => $model],
                        'joinType' => 'INNER',
                    ]);
                }
            } catch (\Exception $e) {
                $this->log(sprintf(
                    'Failed to setup association for model %s: %s',
                    $model,
                    $e->getMessage()
                ), 'error');
            }
        }
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->uuid('foreign_key')
            ->notEmptyString('foreign_key', __('A foreign key is required.'));

        $validator
            ->scalar('model')
            ->maxLength('model', 20)
            ->notEmptyString('model')
            ->add('model', 'validModel', [
                'rule' => function ($value, $context) {
                    return (bool)App::className($value, 'Model/Table', 'Table');
                },
                'message' => __('Invalid model name.'),
            ]);

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->regex(
                'slug',
                '/^[a-z0-9-]+$/',
                __('The slug must be URL-safe (only lowercase letters, numbers, and hyphens)')
            )
            ->add('slug', 'unique', [
                'rule' => function ($value, $context) {
                    return $this->isUniqueSlug($value, $context['data']['foreign_key'] ?? null);
                },
                'message' => __('This slug is already in use.'),
            ]);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(function ($entity) {
            $modelName = $entity->get('model');
            try {
                $table = TableRegistry::getTableLocator()->get($modelName);
                return $table->exists(['id' => $entity->get('foreign_key')]);
            } catch (\Exception $e) {
                return false;
            }
        }, 'validForeignKey', [
            'errorField' => 'foreign_key',
            'message' => __('Invalid foreign key for the specified model.'),
        ]);

        return $rules;
    }

    /**
     * Checks if a slug is unique across all models.
     *
     * @param string $slug The slug to check
     * @param string|null $foreignKey The foreign key to exclude
     * @return bool
     */
    protected function isUniqueSlug(string $slug, ?string $foreignKey): bool
    {
        $conditions = ['slug' => $slug];

        if ($foreignKey !== null) {
            $conditions['foreign_key !='] = $foreignKey;
        }

        return !$this->exists($conditions);
    }

    /**
     * After save callback.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
     * @param \ArrayObject $options The options passed to the save method
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        Cache::delete(self::CACHE_MODELS_KEY, self::CACHE_CONFIG);
    }

    /**
     * After delete callback.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered
     * @param \Cake\Datasource\EntityInterface $entity The entity that was deleted
     * @param \ArrayObject $options The options passed to the delete method
     * @return void
     */
    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        Cache::delete(self::CACHE_MODELS_KEY, self::CACHE_CONFIG);
    }

    /**
     * Finder method for retrieving a record by its slug and model.
     *
     * @param \Cake\ORM\Query $query The query object
     * @param array $options The options containing slug and model
     * @return \Cake\ORM\Query
     */
    public function findBySlug($query, array $options)
    {
        return $query->where([
            'slug' => $options['slug'] ?? '',
            'model' => $options['model'] ?? '',
        ]);
    }
}