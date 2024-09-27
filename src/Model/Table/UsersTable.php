<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Service\RabbitMQService;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Users/profile/',
            'field' => 'profile'
        ]);

        $this->addBehavior('Timestamp');

        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'profile' => [
                'fields' => [
                    'dir' => 'picture_dir',
                    'size' => 'picture_size',
                    'type' => 'picture_type',
                ],
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    return uniqid('', true);
                },
                'deleteCallback' => function ($path, $entity, $field, $settings) {
                    $paths = [
                        $path . $entity->{$field},
                    ];
                    foreach (Configure::read('ImageSizes') as $width) {
                        $paths[] = $path . $entity->{$field} . '_' . $width;
                    }

                    return $paths;
                },
                'keepFilesOnDelete' => false,
            ],
        ]);

        $this->hasMany('Articles', [
            'foreignKey' => 'user_id',
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
            ->scalar('username')
            ->maxLength('username', 50)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->allowEmptyString('password');

        $validator
            ->scalar('confirm_password')
            ->maxLength('confirm_password', 255)
            ->requirePresence('confirm_password', 'create')
            ->notEmptyString('confirm_password')
            ->sameAs('confirm_password', 'password', 'Passwords do not match');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->allowEmptyFile('profile')
            ->add('profile', [
                'mimeType' => [
                    'rule' => ['mimeType', ['image/jpeg', 'image/png', 'image/gif']],
                    'message' => 'Please upload only images (jpeg, png, gif).',
                ],
                'fileSize' => [
                    'rule' => ['fileSize', '<=', '20MB'],
                    'message' => 'Image must be less than 20MB.',
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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
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
        if (!$entity->isNew() && $entity->isDirty('profile')) {
            $originalFilePath = $entity->getOriginal('profile');
            $fullOriginalFilePath = WWW_ROOT . 'files/Users/profile/' . $originalFilePath;
            // Delete the old file if it exists
            if ($originalFilePath && file_exists($fullOriginalFilePath)) {
                unlink($fullOriginalFilePath);
            }
            //delete all the resized versions too
            foreach (Configure::read('ImageSizes') as $width) {
                if (file_exists($fullOriginalFilePath . '_' . $width)) {
                    unlink($fullOriginalFilePath . '_' . $width);
                }
            }
        }

        return true;
    }
}
