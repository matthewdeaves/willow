<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Utility\Text;
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
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Images/image/',
            'field' => 'image',
        ]);

        $this->addBehavior('Timestamp');

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
        $validator->notEmptyString('name', 'Name cannot be empty');

        return $validator;
    }

    /**
     * Validation rules for creating a new image.
     *
     * Extends the default validation rules and adds specific requirements for image creation:
     * - Requires the presence of an image file
     * - Validates the image file mime type (JPEG, PNG, or GIF)
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationCreate(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator
            ->requirePresence('image', 'create')
            ->notEmptyFile('image', 'An image file is required')
            ->add('image', [
                'mimeType' => [
                    'rule' => ['mimeType', ['image/jpeg', 'image/png', 'image/gif']],
                    'message' => 'Please upload only jpeg, png, or gif images.',
                ],
                'fileSize' => [
                    'rule' => ['fileSize', '<=', '10MB'],
                    'message' => 'Image must be less than 10MB.',
                ],
            ]);

        return $validator;
    }

    /**
     * Validation rules for updating an existing image.
     *
     * Extends the default validation rules and adds specific requirements for image updates:
     * - Allows the image file to be empty (no change)
     * - If a new image is provided, validates the mime type (JPEG, PNG, or GIF)
     * - Mime type validation only occurs when a new file is successfully uploaded
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationUpdate(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator
            ->allowEmptyFile('image')
            ->add('image', [
                'mimeType' => [
                    'rule' => ['mimeType', ['image/jpeg', 'image/png', 'image/gif']],
                    'message' => 'Please upload only jpeg, png, or gif images.',
                    'on' => function ($context) {
                        return !empty($context['data']['image'])
                        && $context['data']['image']->getError() === UPLOAD_ERR_OK;
                    },
                ],
                'fileSize' => [
                    'rule' => ['fileSize', '<=', '10MB'],
                    'message' => 'Image must be less than 10MB.',
                ],
            ]);

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
        if (!$entity->isNew() && $entity->isDirty('image')) {
            $originalFilePath = $entity->getOriginal('image');
            $fullOriginalFilePath = WWW_ROOT . 'files/Images/image/' . $originalFilePath;
            // Delete the old file if it exists
            if ($originalFilePath && file_exists($fullOriginalFilePath)) {
                unlink($fullOriginalFilePath);
            }
            //delete all the resized versions too
            foreach (SettingsManager::read('ImageSizes') as $width) {
                if (file_exists($fullOriginalFilePath . '_' . $width)) {
                    unlink($fullOriginalFilePath . '_' . $width);
                }
            }
        }

        return true;
    }
}
