<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\Queue\QueueManager;
use Cake\Validation\Validator;
use Exception;

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
        if (SettingsManager::read('AI.enabled')) {
            try {
                QueueManager::push('App\Job\TagSeoUpdateJob', [
                    'args' => [[
                        'id' => $entity->id,
                        'title' => $entity->title,
                    ]],
                ]);
                $this->log(
                    __('Queue tag SEO update job for Tag:{0}', [$entity->title]),
                    'info',
                    ['group_name' => 'tag_seo_update']
                );
            } catch (Exception $e) {
                $this->log(
                    'Failed to queue tag SEO update job: ' . $e->getMessage(),
                    'error',
                    ['group_name' => 'tag_seo_update']
                );
            }
        }
    }
}
