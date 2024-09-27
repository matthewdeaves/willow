<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Images Model
 *
 * @method \App\Model\Entity\Image newEmptyEntity()
 * @method \App\Model\Entity\Image newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Image> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Image get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Image findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Image patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Image> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Image|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Image saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Image>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Image>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Image>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Image> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Image>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Image>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Image>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Image> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImagesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.use Cake\Event\EventInterface;
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('images');
        $this->setDisplayField('path');
        $this->setPrimaryKey('id');

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Images/path/',
            'field' => 'path',
        ]);

        $this->addBehavior('Timestamp');

        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'path' => [
                'fields' => [
                    'dir' => 'image_dir',
                    'size' => 'image_size',
                    'type' => 'image_type',
                ],
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    return uniqid('', true);
                },
                'deleteCallback' => function ($path, $entity, $field, $settings) {
                    $paths = [
                        $path . $entity->{$field},
                    ];
                    foreach (Configure::read('SiteSettings.ImageSizes') as $width) {
                        $paths[] = $path . $entity->{$field} . '_' . $width;
                    }

                    return $paths;
                },
                'keepFilesOnDelete' => false,
            ],
        ]);
    }

    /**
     * Default validation rules for the Images table.
     *
     * This method sets up the validation rules for the 'name' and 'path' fields of the Images table.
     * It ensures that:
     * - The 'name' field is not empty.
     * - An image file is required when creating a new record.
     * - The uploaded file is of a valid image type (jpeg, png, or gif).
     * - The file size does not exceed 20MB.
     * - The 'path' field can be empty when updating an existing record.
     *
     * @param \Cake\Validation\Validator $validator The validator instance.
     * @return \Cake\Validation\Validator The modified validator instance.
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name', 'Name cannot be empty')
            ->uploadedFile('path', [
                'types' => ['image/jpeg', 'image/png', 'image/gif'],
                'message' => __('Please upload only images (jpeg, png, gif).'),
            ])
            ->add('path', 'fileSize', [
                'rule' => ['fileSize', '<=', '20MB'],
                'message' => __('Image must be less than 20MB.'),
            ])
            ->requirePresence('path', 'create')
            ->allowEmptyFile('path', __('Path can be empty on edit', 'update'));

        return $validator;
    }

    /**
     * beforeSave called to do:
     * 1) On edit with file upload ensure we delete the old image(s)
     *
     * @param \Cake\Event\EventInterface $event The rules object to be modified.
     * @param \Cake\Datasource\EntityInterface $entity The rules object to be modified.
     * @param \ArrayObject $options The rules object to be modified.
     * @return bool True if the save should continue
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): bool
    {
        //if editing an Image with new upload
        if (!$entity->isNew() && $entity->isDirty('path')) {
            $originalFilePath = $entity->getOriginal('path');
            $fullOriginalFilePath = WWW_ROOT . 'files/Images/path/' . $originalFilePath;
            // Delete the old file if it exists
            if ($originalFilePath && file_exists($fullOriginalFilePath)) {
                unlink($fullOriginalFilePath);
            }
            //delete all the resized versions too
            foreach (Configure::read('SiteSettings.ImageSizes') as $width) {
                if (file_exists($fullOriginalFilePath . '_' . $width)) {
                    unlink($fullOriginalFilePath . '_' . $width);
                }
            }
        }

        return true;
    }
}
