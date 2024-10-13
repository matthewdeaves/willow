<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Slugs Model
 *
 * @property \App\Model\Table\ArticlesTable&\Cake\ORM\Association\BelongsTo $Articles
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

        $this->belongsTo('Articles', [
            'foreignKey' => 'article_id',
            'joinType' => 'INNER',
        ]);
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
            ->uuid('article_id')
            ->notEmptyString('article_id');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug');

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
        $rules->add($rules->existsIn(['article_id'], 'Articles'), ['errorField' => 'article_id']);

        return $rules;
    }

    /**
     * Ensures that a slug exists for a given article ID. If the slug does not exist, it creates a new slug entity
     * and attempts to save it. Logs an error message if the save operation fails.
     *
     * @param int|string $articleId The ID of the article for which the slug should be ensured.
     * @param string $slug The slug to be checked or created.
     * @return void
     */
    public function ensureSlugExists($articleId, $slug): void
    {
        $existingSlug = $this->find()
            ->where(['article_id' => $articleId, 'slug' => $slug])
            ->first();

        if (!$existingSlug) {
            $newSlug = $this->newEntity([
                'article_id' => $articleId,
                'slug' => $slug,
            ]);

            if (!$this->save($newSlug)) {
                $this->log('Failed to save slug: ' . json_encode($newSlug->getErrors()), 'error');
            }
        }
    }
}
