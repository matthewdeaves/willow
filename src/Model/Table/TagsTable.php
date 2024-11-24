<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Table;
use Cake\Queue\QueueManager;
use Cake\Utility\Text;
use Cake\Validation\Validator;

/**
 * Tags Model
 *
 * @property \App\Model\Table\ArticlesTable&\Cake\ORM\Association\BelongsToMany $Articles
 * @method \App\Model\Entity\Tag newEmptyEntity()
 * @method \App\Model\Entity\Tag newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Tag> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Tag get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Tag findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Tag> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Tag|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Tag saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Tag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Tag>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Tag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Tag> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Tag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Tag>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Tag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Tag> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TagsTable extends Table
{
    use LogTrait;
    use TranslateTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('tags');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('Tree');

        $this->belongsTo('ParentTag', [
            'className' => 'Tags',
            'foreignKey' => 'parent_id',
        ]);

        $this->addBehavior('Sluggable', [
            'field' => 'title',
            'slug' => 'slug',
            'maxLength' => 255,
        ]);

        $this->addBehavior('Translate', [
            'fields' => [
                'title',
                'description',
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

        $this->belongsToMany('Articles', [
            'foreignKey' => 'tag_id',
            'targetForeignKey' => 'article_id',
            'joinTable' => 'articles_tags',
        ]);

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Tags/image/',
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
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        return $validator;
    }

    /**
     * After save callback.
     *
     * This method is triggered after a tag entity is saved. It queues a TagSeoUpdateJob
     * if AI settings are enabled.
     *
     * @param \Cake\Event\EventInterface $event The afterSave event that was fired.
     * @param \Cake\Datasource\EntityInterface $entity The tag entity that was saved.
     * @param \ArrayObject $options The options passed to the save method.
     * @return void
     * @throws \Exception If there is an error while queueing the SEO update job.
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // noMessage flag will be true if save came from a Job (stops looping)
        $noMessage = $options['noMessage'] ?? false;
        if (SettingsManager::read('AI.enabled') && !$noMessage) {
            $data = [
                'id' => $entity->id,
                'title' => $entity->title,
            ];

            if (SettingsManager::read('AI.tagSEO') && !empty($this->emptySeoFields($entity))) {
                $this->queueJob('App\Job\TagSeoUpdateJob', $data);
            }

            if (SettingsManager::read('AI.tagTranslations')) {
                $this->queueJob('App\Job\TranslateTagJob', $data);
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
            'description',
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
     * This method is used to queue jobs for various tasks related to tags, such as updating SEO fields
     * and translating tags. It uses the QueueManager to push the job into the queue and logs the queued
     * job with relevant information.
     *
     * @param string $job The fully qualified class name of the job to be queued.
     * @param array $data An associative array of data to be passed to the job. Typically includes:
     *                    - 'id' (int): The ID of the tag associated with the job.
     *                    - 'title' (string): The title of the tag.
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
     * Retrieves a simple threaded array of tags.
     *
     * This method performs a 'threaded' find operation on the current model, selecting
     * the 'id', 'title', and 'parent_id' fields. It then processes the result set to
     * create an associative array where each key is a tag title and its value is an
     * array of titles of its direct children.
     *
     * @return array An associative array where keys are tag titles and values are arrays
     *               of titles of their direct children.
     */
    public function getSimpleThreadedArray(): array
    {
        return $this->find('threaded')
        ->select(['id', 'title', 'parent_id'])
        ->all()
        ->reduce(function ($accumulator, $tag) {
            $childrenTitles = array_map(function ($child) {
                return $child->title;
            }, $tag->children);
            $accumulator[$tag->title] = $childrenTitles;

            return $accumulator;
        }, []);
    }

    /**
     * Retrieves root tags from the database.
     *
     * This method fetches tags that have no parent (i.e., root tags) and allows for additional conditions
     * to be applied to the query. The results are ordered by the 'lft' field in ascending order and cached
     * using the specified cache key.
     *
     * @param string $cacheKey The key used for caching the query results. The cache key is appended with 'root_tags'.
     * @param array $additionalConditions Optional array of additional query conditions to apply
     * @return array<\App\Model\Entity\Tag> List of Tag entities that are root level (no parent)
     * @throws \Cake\Database\Exception\DatabaseException When there is a database error
     * @throws \RuntimeException When the cache engine is not properly configured
     */
    public function getRootTags(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Tags.parent_id IS' => null,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'root_tags', 'articles');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves tags that are marked for display in the main menu.
     *
     * This method fetches all tags that have main_menu flag set to true, ordered by their
     * tree position (lft). Results are cached using the provided cache key. Additional
     * conditions can be merged with the base conditions if needed.
     *
     * @param string $cacheKey The base cache key to use for caching the results
     * @param array $additionalConditions Optional array of additional query conditions to apply
     * @return array<\App\Model\Entity\Tag> List of Tag entities matching the criteria
     * @throws \Cake\Database\Exception\DatabaseException When there is a database error
     * @throws \RuntimeException When the cache engine is not properly configured
     */
    public function getMainMenuTags(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Tags.main_menu' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'main_menu_tags', 'articles');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves a hierarchical tree structure of tags.
     *
     * This method fetches tags from the database and organizes them into a nested tree structure.
     * Results are cached for performance using a hash of the conditions as the cache key.
     * The tree is ordered by the 'lft' column to maintain proper hierarchical ordering.
     *
     * @param array $additionalConditions Additional WHERE conditions to filter the tags query
     * @return array Nested array representing the tag hierarchy with each tag containing:
     *               - id: int The tag ID
     *               - parent_id: int|null The parent tag ID
     *               - title: string The tag title
     *               - slug: string URL-friendly version of title
     *               - created: \Cake\I18n\DateTime Creation timestamp
     *               - modified: \Cake\I18n\DateTime Last modified timestamp
     *               - children: array Nested child tags
     * @throws \Cake\Database\Exception\DatabaseException When the database query fails
     * @throws \RuntimeException When cache writing fails
     */
    public function getTagTree(array $additionalConditions = []): array
    {
        $conditions = [];
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
            ])
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'tag_tree', 'articles');

        return $query->find('threaded')->toArray();
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
}
