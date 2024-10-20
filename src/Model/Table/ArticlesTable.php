<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Table\Trait\ArticleCacheTrait;
use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Queue\QueueManager;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use DateTime;
use Exception;
use InvalidArgumentException;
use Cake\Log\LogTrait;

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
    use ArticleCacheTrait;
    use LogTrait;

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

        $this->addBehavior('ImageAssociable');

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Articles/image/',
            'field' => 'image',
        ]);

        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'image' => [
                'fields' => [
                    'dir' => 'dir',
                    'size' => 'size',
                    'type' => 'mime',
                ],
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    $file = $entity->{$field};
                    $clientFilename = $file->getClientFilename();
                    $ext = pathinfo($clientFilename, PATHINFO_EXTENSION);

                    return Text::uuid() . '.' . strtolower($ext);
                },
                'deleteCallback' => function ($path, $entity, $field, $settings) {
                    $paths = [
                        $path . $entity->{$field},
                    ];

                    foreach (SettingsManager::read('ImageSizes') as $width) {
                        $paths[] = $path . $width . DS . $entity->{$field};
                    }

                    return $paths;
                },
                'keepFilesOnDelete' => false,
            ],
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'article_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'articles_tags',
        ]);

        $this->hasMany('PageViews', [
            'foreignKey' => 'article_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

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
                __('The slug must be URL-safe (only lowercase letters, numbers, and hyphens)')
            )
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'uniqueInArticles', [
                'rule' => function ($value, $context) {
                    $exists = $this->exists(['slug' => $value]);
                    if ($exists && isset($context['data']['id'])) {
                        $exists = $this->exists(['slug' => $value, 'id !=' => $context['data']['id']]);
                    }

                    return !$exists;
                },
                'message' => __('This slug is already in use in articles. Please enter a unique slug.'),
            ])
            ->add('slug', 'uniqueInSlugs', [
                'rule' => function ($value, $context) {
                    $slugsTable = TableRegistry::getTableLocator()->get('Slugs');
                    $exists = $slugsTable->exists(['slug' => $value]);
                    if ($exists && isset($context['data']['id'])) {
                        $exists = $slugsTable->exists(['slug' => $value, 'article_id !=' => $context['data']['id']]);
                    }

                    return !$exists;
                },
                'message' => __('Slug conflicts with an existing SEO redirect. Please choose a different slug.'),
            ]);

        $validator
            ->allowEmptyFile('image')
            ->add('image', [
                'mimeType' => [
                    'rule' => ['mimeType', ['image/jpeg', 'image/png', 'image/gif']],
                    'message' => __('Please upload only images (jpeg, png, gif).'),
                ],
                'fileSize' => [
                    'rule' => ['fileSize', '<=', '5MB'],
                    'message' => __('Image must be less than 5MB.'),
                ],
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
     * This method is triggered before an entity is saved. It performs several operations:
     * 1. Generates a slug from the entity's title if the entity is new and the slug is not set.
     *    - The slug is trimmed to a maximum length of 255 characters.
     *    - Checks if the generated slug is unique. If not, sets an error on the entity and prevents saving.
     * 2. Updates the 'published' field based on changes to the 'is_published' field.
     *    - Sets the 'published' date to the current date and time if 'is_published' changes from 0 to 1.
     *    - Sets the 'published' field to null if 'is_published' changes from 1 to 0.
     * 3. Calculates the word count of the 'body' field if it is set or modified.
     *    - Strips HTML tags from the body and counts the words, storing the result in 'word_count'.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered.
     * @param \Cake\Datasource\EntityInterface $entity The entity that is being saved.
     * @param \ArrayObject $options Additional options for the save operation.
     * @return bool|null Returns false to prevent the save operation if the slug is not unique, true otherwise.
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

        // Check if is_published has changed to published
        if ($entity->isDirty('is_published') && $entity->is_published) {
            $entity->published = new DateTime('now');
        }

        // Calculate word count if body is set or modified
        if ($entity->isDirty('body') || ($entity->isNew() && !empty($entity->body))) {
            $strippedBody = strip_tags($entity->body);
            $wordCount = str_word_count($strippedBody);
            $entity->word_count = $wordCount;
        }

        return true;
    }

    /**
     * After save callback.
     *
     * This method is triggered after an entity is saved. It performs two main operations:
     * 1. Ensures that a published article has a history of slugs.
     * 2. Queues an SEO update job for published articles if AI settings are enabled.
     *
     * @param \Cake\Event\EventInterface $event The afterSave event that was fired.
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved.
     * @param \ArrayObject $options The options passed to the save method.
     * @return void
     * @throws \Exception If there is an error while queueing the SEO update job.
     * @uses \App\Model\Table\SlugsTable::ensureSlugExists()
     * @uses \App\Utility\SettingsManager::read()
     * @uses \Cake\Queue\QueueManager::push()
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Make sure a published Article has a history of slugs
        if ($entity->is_published) {
            $this->Slugs->ensureSlugExists($entity->id, $entity->slug);
        }

        // Queue an Article SEO update job
        if ($entity->is_published && SettingsManager::read('AI.enabled')) {
            try {
                QueueManager::push('App\Job\ArticleSeoUpdateJob', [
                    'args' => [[
                        'id' => $entity->id,
                        'title' => $entity->title,
                    ]],
                ]);
                $this->log(__('Queued Article SEO update job: {0}', 
                    [$entity->title]), 
                    'info', 
                    ['group_name' => 'article_seo_update']
                );
            } catch (Exception $e) {
                $this->log(__('Failed to queue Article SEO update job: {0}'. [$e->getMessage()]),
                    'error',
                    ['group_name' => 'article_seo_update']
                );
            }
        }

        // Queue an Article Tag Update job
        try {
            QueueManager::push('App\Job\ArticleTagUpdateJob', [
                'args' => [[
                    'id' => $entity->id,
                    'title' => $entity->title,
                ]],
            ]);
            $this->log(__('Queued Article Tag update job: {0}', 
                [$entity->title]), 
                'info', 
                ['group_name' => 'article_tag_update']
            );
        } catch (Exception $e) {
            $this->log(__('Failed to queue Article Tag update job: {0}'. [$e->getMessage()]),
                'error',
                ['group_name' => 'article_tag_update']
            );
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
        $oldParentId = $article->parent_id;

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

        // Clear cache for the moved article
        $this->clearFromCache($article->slug);

        // Clear cache for the old parent (if it exists)
        if ($oldParentId) {
            $oldParent = $this->get($oldParentId);
            $this->clearFromCache($oldParent->slug);
        }

        // Clear cache for the new parent (if it's not root)
        if ($article->parent_id) {
            $newParent = $this->get($article->parent_id);
            $this->clearFromCache($newParent->slug);
        }

        // Clear cache for affected siblings
        foreach ($siblings as $sibling) {
            $this->clearFromCache($sibling->slug);
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
