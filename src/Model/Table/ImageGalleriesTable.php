<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Queue\QueueManager;
use Cake\Validation\Validator;
use Exception;

/**
 * ImageGalleries Model
 *
 * @property \App\Model\Table\ImagesTable&\Cake\ORM\Association\BelongsToMany $Images
 * @method \App\Model\Entity\ImageGallery newEmptyEntity()
 * @method \App\Model\Entity\ImageGallery newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ImageGallery> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ImageGallery get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ImageGallery findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ImageGallery patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ImageGallery> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ImageGallery|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ImageGallery saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGallery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGallery>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGallery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGallery> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGallery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGallery>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGallery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGallery> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImageGalleriesTable extends Table
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

        $this->setTable('image_galleries');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Slug', [
            'sourceField' => 'name',
            'targetField' => 'slug',
            'maxLength' => 255,
        ]);

        $this->hasMany('ImageGalleriesImages', [
            'foreignKey' => 'image_gallery_id',
            'dependent' => true,
        ]);

        $this->belongsToMany('Images', [
            'foreignKey' => 'image_gallery_id',
            'targetForeignKey' => 'image_id',
            'joinTable' => 'image_galleries_images',
            'through' => 'ImageGalleriesImages',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->allowEmptyString('slug');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->boolean('is_published')
            ->notEmptyString('is_published');

        $validator
            ->uuid('created_by')
            ->allowEmptyString('created_by');

        $validator
            ->uuid('modified_by')
            ->allowEmptyString('modified_by');

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
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }

    /**
     * afterSave callback - Queue preview generation job when gallery changes
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The gallery entity
     * @param \ArrayObject $options Options for the save operation
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Queue preview generation for new galleries or when images might have changed
        if ($entity->isNew() || $entity->isDirty('name') || $entity->isDirty('description')) {
            $this->queuePreviewGeneration($entity->id);
        }
    }

    /**
     * beforeDelete callback - Clean up preview image file
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The gallery entity
     * @param \ArrayObject $options Options for the delete operation
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Clean up preview image file
        if ($entity->preview_image) {
            $previewPath = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS . $entity->preview_image;
            if (file_exists($previewPath)) {
                unlink($previewPath);
            }
        }

        // Also clean up gallery ID-based file
        $galleryPreviewPath = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS . $entity->id . '.jpg';
        if (file_exists($galleryPreviewPath)) {
            unlink($galleryPreviewPath);
        }
    }

    /**
     * Queue preview generation job for a gallery
     *
     * @param string $galleryId Gallery ID
     * @return void
     */
    public function queuePreviewGeneration(string $galleryId): void
    {
        try {
            QueueManager::push('App\\Job\\GenerateGalleryPreviewJob', [
                'gallery_id' => $galleryId,
            ]);

            $this->log(
                sprintf('Queued preview generation job for gallery: %s', $galleryId),
                'info',
                ['group_name' => 'App\\Model\\Table\\ImageGalleriesTable'],
            );
        } catch (Exception $e) {
            $this->log(
                sprintf('Failed to queue preview generation for gallery %s: %s', $galleryId, $e->getMessage()),
                'error',
                ['group_name' => 'App\\Model\\Table\\ImageGalleriesTable'],
            );
        }
    }

    /**
     * Check if gallery images have changed since last save
     *
     * @param \Cake\Datasource\EntityInterface $entity Gallery entity
     * @return bool True if images have changed
     */
    private function imagesChanged(EntityInterface $entity): bool
    {
        // This is a basic check - in practice, image changes happen via the junction table
        // The ImageGalleriesImagesTable will handle queuing preview regeneration
        return $entity->isDirty('images') || $entity->isDirty('_joinData');
    }
}
