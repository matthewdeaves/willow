<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Setting;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Settings Model
 *
 * @method \App\Model\Entity\Setting newEmptyEntity()
 * @method \App\Model\Entity\Setting newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Setting> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Setting get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Setting findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Setting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Setting> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Setting|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Setting saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Setting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setting>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setting> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setting>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setting> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SettingsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('settings');
        $this->setDisplayField('category');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->scalar('category')
            ->maxLength('category', 255)
            ->requirePresence('category', 'create')
            ->notEmptyString('category');

        $validator
            ->scalar('subcategory')
            ->maxLength('subcategory', 255)
            ->allowEmptyString('subcategory');

        $validator
            ->scalar('key_name')
            ->maxLength('key_name', 255)
            ->requirePresence('key_name', 'create')
            ->notEmptyString('key_name');

        $validator
            ->boolean('is_numeric')
            ->allowEmptyString('is_numeric');

        $validator
            ->requirePresence('value', 'create')
            ->notEmptyString('value', 'A value is required');

        return $validator;
    }

    /**
     * Before save event handler.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @param \App\Model\Entity\Setting $entity The entity instance.
     * @param \ArrayObject $options The options passed to the save method.
     * @return bool
     */
    public function beforeSave(EventInterface $event, Setting $entity, ArrayObject $options): bool
    {
        if ($entity->isNew()) {
            return true;
        }

        $originalEntity = $this->get($entity->id);
        if ($originalEntity->is_numeric == 1 && !is_numeric($entity->value)) {
            $entity->setError('value', 'The value must be a number for this setting.');

            return false;
        }

        if ($originalEntity->is_numeric == 0 && empty($entity->value)) {
            $entity->setError('value', 'The value must not be empty for this setting.');

            return false;
        }

        return true;
    }

    /**
     * Retrieves setting values based on the specified category and optional key name.
     *
     * This method can fetch either all settings for a given category or a specific setting
     * within a category, depending on whether a key name is provided.
     *
     * @param string $category The category of the setting(s) to retrieve.
     * @param string|null $keyName The specific key name of the setting. If null, all settings for the category are returned.
     * @return mixed Returns one of the following:
     *               - An associative array of all settings for the category if $keyName is null.
     *               - The value of the specific setting if $keyName is provided and the setting exists.
     *               - null if a specific setting is requested but not found.
     * @throws \Cake\Database\Exception\DatabaseException If there's an issue with the database query.
     */
    public function getSettingValue(string $category, ?string $keyName = null): mixed
    {
        if ($keyName === null) {
            // Fetch all settings for the category
            return $this->find()
                ->where(['category' => $category])
                ->all()
                ->combine('key_name', 'value')
                ->toArray();
        }

        // Fetch a single setting
        $setting = $this->find()
            ->where(['category' => $category, 'key_name' => $keyName])
            ->first();

        return $setting ? $setting->value : null;
    }
}
