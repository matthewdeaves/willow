<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Core\Configure\Engine\DatabaseSettingsManager;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


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
        $this->setDisplayField('key_name');
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
            ->scalar('key_name')
            ->maxLength('key_name', 255)
            ->requirePresence('key_name', 'create')
            ->notEmptyString('key_name');

        $validator
            ->scalar('value')
            ->requirePresence('value', 'create')
            ->notEmptyString('value');

        $validator
            ->scalar('group_name')
            ->maxLength('group_name', 100)
            ->allowEmptyString('group_name');

        $validator->add('value', 'custom', [
            'rule' => function ($value, $context) {
                if ($context['data']['is_numeric']) {
                    return is_numeric($value);
                }
                return is_string($value);
            },
            'message' => 'The value must be numeric when is_numeric is true, otherwise it must be a string.'
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
        $rules->add($rules->isUnique(['key_name', 'group_name'], ['allowMultipleNulls' => true]), ['errorField' => 'key_name']);

        return $rules;
    }

    /**
     * After save callback.
     *
     * This method is called after an entity is saved. It clears the DatabaseSettingsManager
     * cache if the entity is new or has been modified.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
     * @param \ArrayObject $options The options passed to the save method
     * @return void
     */
    public function afterSave(EventInterface $event, $entity, $options)
    {
        if ($entity->isNew() || $entity->isDirty()) {
            DatabaseSettingsManager::clearCache();
        }
    }
}
