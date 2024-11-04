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
}
