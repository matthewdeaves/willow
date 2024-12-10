<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Queue\QueueManager;
use Cake\Validation\Validator;
use DateTime;

/**
 * Articles Table
 *
 * Manages article content with features including:
 * - Multi-language support
 * - SEO metadata
 * - Image handling
 * - Commenting system
 * - Page view tracking
 * - AI-powered content enhancement
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 * @property \Cake\ORM\Association\HasMany $PageViews
 * @property \Cake\ORM\Association\HasMany $Slugs
 * @method \App\Model\Entity\Article newEmptyEntity()
 * @method \App\Model\Entity\Article newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Article[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Article get($primaryKey, $options = [])
 * @method \App\Model\Entity\Article findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Article patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Article[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Article|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 */
class ArticlesTable extends Table
{
    use LogTrait;
    use TranslateTrait;

    /**
     * Initialize method
     *
     * Configures table associations, behaviors, and other settings
     *
     * @param array<string, mixed> $config Configuration array
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('Commentable');

        $this->addBehavior('Orderable');

        $this->addBehavior('Slug');

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
    }

    /**
     * Default validation rules
     *
     * Sets up validation rules for article fields including:
     * - User ID validation
     * - Title requirements
     * - Body content validation
     * - Image upload restrictions
     *
     * @param \Cake\Validation\Validator $validator Validator instance
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
            ->allowEmptyFile('image')
            ->add('image', [
                'mimeType' => [
                    'rule' => ['mimeType', ['image/jpeg', 'image/png', 'image/gif']],
                    'message' => __('Please upload only images (jpeg, png, gif).'),
                ],
                'fileSize' => [
                    'rule' => ['fileSize', '<=', '10MB'],
                    'message' => __('Image must be less than 10MB.'),
                ],
            ]);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating application integrity
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Before save callback
     *
     * Handles:
     * - Setting publication date when article is published
     * - Calculating word count for article body
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options passed to the save method
     * @return bool|null True if the operation should continue, false if it should abort
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): ?bool
    {
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
     * After save callback
     *
     * Handles AI-powered enhancements including:
     * - Article tagging
     * - Summary generation
     * - SEO field population
     * - Content translation
     *
     * @param \Cake\Event\EventInterface $event The afterSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
     * @param \ArrayObject $options The options passed to the save method
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
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
                $entity->kind == 'article' &&
                ((isset($options['regenerateTags']) &&
                $options['regenerateTags'] == 1) ||
                !isset($options['regenerateTags']))
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
     * Checks if any of the SEO fields are empty
     *
     * @param \Cake\Datasource\EntityInterface $entity The article entity to check
     * @return array<string> List of empty SEO field names
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
     * Checks if any of the original language fields for translation are empty
     *
     * @param \Cake\Datasource\EntityInterface $entity The article entity to check
     * @return array<string> List of empty translation field names
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
     * Queues a job with the provided job class and data
     *
     * @param string $job The fully qualified job class name
     * @param array<string, mixed> $data The data to be passed to the job
     * @return void
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

        $cacheKey = hash('xxh3', json_encode($conditions));

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
            ->cache($cacheKey . 'article_page_tree', 'articles');

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
    public function getFeatured(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'article',
            'Articles.featured' => 1,
            'Articles.is_published' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'featured_articles', 'articles');

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
    public function getRootPages(string $cacheKey, array $additionalConditions = []): array
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
            ->cache($cacheKey . 'root_pages', 'articles');

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
    public function getMainMenuPages(string $cacheKey, array $additionalConditions = []): array
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
            ->cache($cacheKey . 'main_menu_pages', 'articles');

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
    public function getArchiveDates(string $cacheKey): array
    {
        $query = $this->find()
            ->select([
                'year' => 'YEAR(published)',
                'month' => 'MONTH(published)',
            ])
            ->where([
                'Articles.is_published' => 1,
                'Articles.kind' => 'article',
                'Articles.published IS NOT' => null,
            ])
            ->group(['year', 'month'])
            ->orderBy([
                'year' => 'DESC',
                'month' => 'DESC',
            ])
            ->cache($cacheKey . 'archive_dates', 'articles');

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
    public function getRecentArticles(string $cacheKey, array $additionalConditions = []): array
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
            ->cache($cacheKey . 'recent_articles', 'articles');

        return $query->all()->toArray();
    }
}
