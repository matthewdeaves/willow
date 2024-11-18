<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Table\Trait\ArticleCacheTrait;
use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Queue\QueueManager;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use DateTime;
use InvalidArgumentException;

/**
 * Articles Model
 *
 * This model handles article content including pages, blog posts, and hierarchical content structures.
 * It supports features like slugs, tags, image uploads, and SEO optimization.
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\BelongsToMany $Tags
 * @property \App\Model\Table\PageViewsTable&\Cake\ORM\Association\HasMany $PageViews
 * @property \App\Model\Table\SlugsTable&\Cake\ORM\Association\HasMany $Slugs
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
 * @mixin \App\Model\Behavior\CommentableBehavior
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 * @mixin \App\Model\Behavior\SluggableBehavior
 * @mixin \App\Model\Behavior\ImageAssociableBehavior
 * @mixin \App\Model\Behavior\QueueableImageBehavior
 * @mixin \Josegonzalez\Upload\Model\Behavior\UploadBehavior
 */
class ArticlesTable extends Table
{
    use ArticleCacheTrait;
    use LogTrait;
    use TranslateTrait;

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

        $this->addBehavior('Translate', [
            'fields' => [
                'title',
                'body',
                'summary',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'facebook_description',
                'linkedin_description',
                'instagram_description',
                'twitter_description',
            ],
            'defaultLocale' => 'en_GB',
            'allowEmptyTranslations' => false,
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
     * Sets up validation rules for article fields including:
     * - user_id: Must be a valid UUID and not empty
     * - title: Required string, max 255 characters
     * - body: Optional text content
     * - slug: Required URL-safe string, must be unique across articles and slugs
     * - image: Optional file upload with mime type and size restrictions
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
     * Implements the following rules:
     * - Validates that user_id exists in Users table
     * - Ensures slug is unique across articles
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

        // noMessage flag will be true if save came from a Job (stops looping)
        $noMessage = $options['noMessage'] ?? false;

        // All Articles should be tagged from the start
        if (
            SettingsManager::read('AI.enabled')
            && !$noMessage
        ) {
            $data = [
                'id' => $entity->id,
                'title' => $entity->title,
            ];

            if (
                (isset($options['regenerateTags']) && $options['regenerateTags'] == 1)
                || !isset($options['regenerateTags'])
            ) {
                // Queue up an ArticleTagUpdateJob
                if (SettingsManager::read('AI.articleTags')) {
                    $this->queueJob('App\Job\ArticleTagUpdateJob', $data);
                }
            }

            // Queue up an ArticleSummaryUpdateJob
            if (SettingsManager::read('AI.articleSummaries') && empty($entity->summary)) {
                $this->queueJob('App\Job\ArticleSummaryUpdateJob', $data);
            }
        }

        // Published Articles should be SEO ready with translations
        if (
            $entity->is_published
            && SettingsManager::read('AI.enabled')
            && !$noMessage
        ) {
            $data = [
                'id' => $entity->id,
                'title' => $entity->title,
            ];

            // Queue a job to update the Article SEO fields
            if (SettingsManager::read('AI.articleSEO') && !empty($this->emptySeoFields($entity))) {
                $this->queueJob('App\Job\ArticleSeoUpdateJob', $data);
            }

            // Queue a job to translate the Article
            if (SettingsManager::read('AI.articleTranslations')) {
                $this->queueJob('App\Job\TranslateArticleJob', $data);
            }
        }
    }

    /**
     * Checks if any of the SEO fields are empty.
     *
     * @return array Returns an array of empty SEO fields.
     */
    public function emptySeoFields(EntityInterface $entity): array
    {
        $seoFields = [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'facebook_description',
            'linkedin_description',
            'twitter_description',
            'instagram_description',
        ];

        return array_filter($seoFields, fn ($field) => empty($entity->{$field}));
    }

    /**
     * Checks if any of the original language fields for translation are empty.
     *
     * @return array Returns an array of empty SEO fields.
     */
    public function emptyTranslationFields(EntityInterface $entity): array
    {
        if ($this->behaviors()->has('Translate')) {
            // Get the configuration of the Timestamp behavior
            $config = $this->behaviors()->get('Translate')->getConfig();

            return array_filter($config['fields'], fn ($field) => empty($entity->{$field}));
        }

        return [];
    }

    /**
     * Queues a job with the provided job class and data.
     *
     * This method is used to queue jobs for various tasks related to articles, such as updating SEO fields,
     * translating articles, updating tags, and generating summaries. It uses the QueueManager to push the
     * job into the queue and logs the queued job with relevant information.
     *
     * @param string $job The fully qualified class name of the job to be queued.
     * @param array $data An associative array of data to be passed to the job. Typically includes:
     *                    - 'id' (int): The ID of the article associated with the job.
     *                    - 'title' (string): The title of the article.
     * @return void
     * @throws \Exception If there is an error while queueing the job.
     * @uses \Cake\Queue\QueueManager::push() Pushes the job into the queue.
     * @uses \Cake\Log\Log::info() Logs the queued job with relevant information.
     */
    public function queueJob(string $job, array $data): void
    {
        QueueManager::push($job, $data);
        $this->log(
            sprintf(
                'Queued a %s with data: %s',
                $job,
                json_encode($data),
            ),
            'info',
            ['group_name' => $job]
        );
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
     * Returns a nested array of pages with their relationships and metadata including:
     * - Basic page information (id, title, slug)
     * - Publication status
     * - Creation and modification dates
     * - Page view counts
     * - Hierarchical structure (parent-child relationships)
     *
     * @param array $additionalConditions Optional additional query conditions
     * @return array Hierarchical array of pages with nested children
     */
    public function getPageTree(array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'page',
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
            ->orderBy(['lft' => 'ASC'])
            ->cache('article_page_tree', 'articles');

        return $query->find('threaded')->toArray();
    }

    /**
     * Retrieves a list of featured articles with optional additional conditions.
     *
     * This method constructs a query to find articles that are marked as featured.
     * Additional conditions can be provided to further filter the results.
     * The results are ordered by the 'lft' field in ascending order.
     *
     * @param array $additionalConditions An array of additional conditions to apply to the query.
     * @return array A list of featured articles that match the specified conditions.
     */
    public function getFeatured(array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.featured' => 1,
            'Articles.is_published' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache('featured_articles', 'articles');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves a list of root pages from the Articles table.
     *
     * This method fetches articles that are categorized as 'page', have no parent (i.e., root pages),
     * and are published. Additional conditions can be provided to further filter the results.
     *
     * @param array $additionalConditions An associative array of additional conditions to apply to the query.
     *                                    These conditions will be merged with the default conditions.
     * @return array An array of root pages that match the specified conditions,
     * ordered by the 'lft' field in ascending order.
     */
    public function getRootPages(array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'page',
            'Articles.parent_id IS' => null,
            'Articles.is_published' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache('root_pages', 'articles');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves published pages marked for display in the main menu.
     *
     * This method fetches articles that meet the following criteria:
     * - Are of type 'page'
     * - Are published (is_published = 1)
     * - Are marked for main menu display (main_menu = 1)
     * Results are ordered by the 'lft' field for proper tree structure display.
     * Results are cached using the 'main_menu_pages' key in the 'articles' cache config.
     *
     * @param array $additionalConditions Additional conditions to merge with the default query conditions
     * @return array List of Article entities matching the criteria
     * @throws \Cake\Database\Exception\DatabaseException When the database query fails
     * @throws \Cake\Cache\Exception\InvalidArgumentException When cache configuration is invalid
     */
    public function getMainMenuPages(array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'page',
            'Articles.is_published' => 1,
            'Articles.main_menu' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache('main_menu_pages', 'articles');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Gets an array of years and months that have published articles.
     *
     * This method queries the articles table to find all unique year/month combinations
     * where articles were published, organizing them in a hierarchical array structure
     * with years as keys and months as values. Results are cached using the 'articles'
     * cache configuration to improve performance.
     *
     * @return array An array where keys are years and values are arrays of month numbers
     *              that have published articles, sorted in descending order.
     */
    public function getArchiveDates(): array
    {
        $query = $this->find()
            ->select([
                'year' => 'YEAR(published)',
                'month' => 'MONTH(published)',
            ])
            ->where([
                'Articles.is_published' => 1,
                'Articles.published IS NOT' => null,
            ])
            ->group(['year', 'month'])
            ->orderBy([
                'year' => 'DESC',
                'month' => 'DESC',
            ])
            ->cache('archive_dates', 'articles');

        $dates = [];
        foreach ($query as $result) {
            $year = $result->year;
            if (!isset($dates[$year])) {
                $dates[$year] = [];
            }
            $dates[$year][] = (int)$result->month;
        }

        return $dates;
    }

    /**
     * Retrieves the most recent published articles.
     *
     * This method queries the Articles table to find articles that are of kind 'article' and are published.
     * It includes associated Users and Tags data, orders the results by the published date in descending order,
     * and limits the results to the top 3 most recent articles.
     *
     * @return array An array of the most recent published articles, including associated Users and Tags data.
     */
    public function getRecentArticles(array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'article',
            'Articles.is_published' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);

        $query = $this->find()
            ->where($conditions)
            ->contain(['Users', 'Tags'])
            ->orderBy(['Articles.published' => 'DESC'])
            ->limit(3)
            ->cache('recent_articles', 'articles');

        return $query->all()->toArray();
    }
}
