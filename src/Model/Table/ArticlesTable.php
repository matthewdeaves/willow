<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use DateTime;
use InvalidArgumentException;

/**
 * Articles Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\BelongsToMany $Tags
 * @method \App\Model\Entity\Article newEmptyEntity()
 * @method \App\Model\Entity\Article newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Article> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Article get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Article findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Article patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Article> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Article|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Article saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Article>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Article> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ArticlesTable extends Table
{
    /**
     * Initialize method for the ArticlesTable.
     *
     * This method sets up the table configuration, behaviors, and associations for the Articles table.
     *
     * @param array $config The configuration array for the table.
     * @return void
     * @uses \Cake\ORM\Table::setTable() Sets the database table name.
     * @uses \Cake\ORM\Table::setDisplayField() Sets the display field for the table.
     * @uses \Cake\ORM\Table::setPrimaryKey() Sets the primary key for the table.
     * @uses \Cake\ORM\Table::addBehavior() Adds behaviors to the table.
     * @uses \Cake\ORM\Table::belongsTo() Sets up a belongsTo association.
     * @uses \Cake\ORM\Table::belongsToMany() Sets up a belongsToMany association.
     * @uses \Cake\ORM\Table::hasMany() Sets up a hasMany association.
     *
     * Behaviors:
     * - Timestamp: Automatically manages created and modified timestamps.
     * - Commentable: Allows articles to have comments.
     * - Tree: Enables tree structure for hierarchical data.
     * - Sluggable: Automatically generates slugs from the title field.
     *
     * Associations:
     * - Users: Articles belong to a User, linked by user_id.
     * - Tags: Articles have a many-to-many relationship with Tags through the articles_tags join table.
     * - PageViews: Articles have many PageViews, linked by article_id.
     * - Slugs: Articles have many Slugs.
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('Commentable');

        $this->addBehavior('Tree');

        $this->addBehavior('Sluggable', [
            'field' => 'title',
            'slug' => 'slug',
            'maxLength' => 255,
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'article_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'articles_tags',
        ]);

        $this->hasMany('PageViews', [
            'foreignKey' => 'article_id',
        ]);

        /**
         * Defines a hasMany association between the current model and the Slugs model.
         *
         * This association indicates that each record in the current model can have multiple associated records
         * in the Slugs model. The association is configured with the following options:
         *
         * - 'dependent' => true: This option ensures that when a record in the current model is deleted,
         *   all associated records in the Slugs model will also be deleted. This is useful for maintaining
         *   referential integrity by automatically removing related data that is no longer needed.
         *
         * - 'cascadeCallbacks' => true: When set to true, this option ensures that callbacks are triggered
         *   during the deletion process of associated records. This means that any logic defined in the
         *   Slugs model's beforeDelete or afterDelete callbacks will be executed when associated records
         *   are deleted. This is important for performing additional cleanup or logging operations.
         *
         * @see https://book.cakephp.org/5/en/orm/associations.html#hasmany-associations
         */
        $this->hasMany('Slugs', [
            'dependent' => true,
            'cascadeCallbacks' => true,
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
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('body')
            ->allowEmptyString('body');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->regex(
                'slug',
                '/^[a-z0-9-]+$/',
                'The slug must be URL-safe (only lowercase letters, numbers, and hyphens)'
            )
            ->requirePresence('slug', 'create')
            ->allowEmptyString('slug')
            ->add('slug', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'This slug is already in use. Please enter a unique slug.',
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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        $rules->add($rules->isUnique(['slug']));

        return $rules;
    }

    /**
     * Before save callback.
     *
     * Generates a unique slug for new entities if not already set.
     * If the generated slug is not unique, it sets an error and prevents the save.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options passed to the save method
     * @return bool|null Returns false if the save should be stopped, true otherwise
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): ?bool
    {
        if ($entity->isNew() && !$entity->slug) {
            $sluggedTitle = strtolower(Text::slug($entity->title));
            // trim slug to maximum length defined in schema
            $entity->slug = substr($sluggedTitle, 0, 255);

            //check generated slug is unique
            $existing = $this->find('all', conditions: ['slug' => $entity->slug])->first();

            if ($existing) {
                // If not unique, set the slug back to the entity for user modification
                $entity->setError('slug', 'The generated slug is not unique. Please modify it.');

                return false; // Prevent save
            }
        }

        // Check if is_published is changing from 0 to 1
        if ($entity->isDirty('is_published')) {
            $originalValue = $entity->getOriginal('is_published');
            if ($originalValue == 0 && $entity->is_published == 1) {
                $entity->published = new DateTime();
            } elseif ($originalValue == 1 && $entity->is_published == 0) {
                $entity->published = null;
            }
        }

        return true;
    }

    /**
     * After save callback.
     *
     * This method is triggered after an entity is saved. It ensures that a published article
     * maintains a history of its slugs. If the article is new and published, or if an existing
     * published article has a modified slug, a new slug entity is created and saved. If saving
     * the slug fails, an error is logged.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered.
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved.
     * @param \ArrayObject $options Additional options for the save operation.
     * @return void
     * @throws \Cake\ORM\Exception\PersistenceFailedException If the slug entity fails to save.
     * @uses \Cake\Datasource\EntityInterface::isNew() To check if the entity is new.
     * @uses \Cake\Datasource\EntityInterface::isDirty() To check if the slug field has been modified.
     * @uses \Cake\ORM\Table::newEntity() To create a new slug entity.
     * @uses \Cake\ORM\Table::save() To save the new slug entity.
     * @uses \Cake\Log\Log::error() To log errors if slug saving fails.
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Make sure a published Article has a history of slugs
        if ($entity->is_published) {
            $this->Slugs->ensureSlugExists($entity->id, $entity->slug);
        }
    }

    /**
     * Reorders an article within a hierarchical structure.
     *
     * This method allows for moving an article to a new parent or to the root level,
     * and adjusts its position among its siblings accordingly.
     *
     * @param array $data An associative array containing:
     *                    - 'id' (int): The ID of the article to be reordered.
     *                    - 'newParentId' (mixed): The ID of the new parent article, or 'root' to move to the root level.
     *                    - 'newIndex' (int): The new position index among siblings.
     * @throws \InvalidArgumentException If the provided data is not an array.
     * @return bool Returns true on successful reordering.
     */
    public function reorder(array $data): bool
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        $article = $this->get($data['id']);

        if ($data['newParentId'] === 'root') {
            // Moving to root level
            $article->parent_id = null;
            $this->save($article);
        } else {
            // Moving to a new parent
            $newParent = $this->get($data['newParentId']);
            $article->parent_id = $newParent->id;
            $this->save($article);
        }

        // Adjust the position within siblings
        if ($article->parent_id === null) {
            // For root level items
            $siblings = $this->find()
                ->where(['parent_id IS' => null])
                ->orderBy(['lft' => 'ASC'])
                ->toArray();
        } else {
            // For non-root items
            $siblings = $this->find('children', for: $article->parent_id, direct: true)
                ->orderBy(['lft' => 'ASC'])
                ->toArray();
        }

        $currentPosition = array_search($article->id, array_column($siblings, 'id'));
        $newPosition = $data['newIndex'];

        if ($currentPosition !== false && $currentPosition !== $newPosition) {
            if ($newPosition > $currentPosition) {
                $this->moveDown($article, $newPosition - $currentPosition);
            } else {
                $this->moveUp($article, $currentPosition - $newPosition);
            }
        }

        return true;
    }

    /**
     * Retrieves a hierarchical tree structure of pages.
     *
     * This method queries the database to fetch articles that are marked as pages
     * (i.e., where 'Articles.is_page' is set to 1). It selects specific fields such as
     * 'id', 'parent_id', 'title', 'slug', 'created', and 'modified', and orders the results
     * by the 'lft' column in ascending order to maintain the tree structure.
     *
     * The method utilizes the 'threaded' finder to organize the results into a nested
     * array format, representing the hierarchical relationships between the pages.
     *
     * @return array An array representing the hierarchical tree of pages, with each node
     *               containing its children in a nested structure.
     */
    public function getPageTree(array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.is_page' => 1,
        ];

        // Merge the default conditions with any additional conditions provided
        $conditions = array_merge($conditions, $additionalConditions);

        $query = $this->find()
            ->select([
                'id',
                'parent_id',
                'title',
                'slug',
                'created',
                'modified',
                'is_published',
                'pageview_count' => $this->PageViews->find()
                    ->where(['PageViews.article_id = Articles.id'])
                    ->select([
                        'count' => $this->PageViews->find()->func()->count('PageViews.id'),
                    ]),
            ])
            ->where($conditions)
            ->orderBy(['lft' => 'ASC']);

        return $query->find('threaded')->toArray();
    }
}
