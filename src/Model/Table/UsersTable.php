<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
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
            'folder_path' => 'files/Users/picture/',
            'field' => 'picture',
        ]);

        $this->addBehavior('Timestamp');

        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'picture' => [
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

        $this->hasMany('Articles', [
            'foreignKey' => 'user_id',
        ]);

        $this->hasMany('Comments', [
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
            ->minLength('password', 8, __('Password must be at least 8 characters long'))
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->allowEmptyString('password', 'update')
            ->notEmptyString('password', null, 'create');

        $validator
            ->scalar('confirm_password')
            ->maxLength('confirm_password', 255)
            ->requirePresence('confirm_password', 'create')
            ->allowEmptyString('confirm_password', 'update')
            ->notEmptyString('confirm_password', null, 'create')
            ->sameAs('confirm_password', 'password', 'Passwords do not match');

        $validator
            ->email('email')
            ->notEmptyString('email');

        $validator
            ->allowEmptyFile('picture')
            ->add('picture', [
                'mimeType' => [
                    'rule' => ['mimeType', ['image/jpeg', 'image/png', 'image/gif']],
                    'message' => 'Please upload only images (jpeg, png, gif).',
                ],
                'fileSize' => [
                    'rule' => ['fileSize', '<=', '5MB'],
                    'message' => 'Image must be less than 5MB.',
                ],
            ]);

        return $validator;
    }

    /**
     * Validation method for resetting passwords.
     *
     * This method defines validation rules for the password reset process.
     * It ensures that the password meets the minimum length requirement
     * and that the password confirmation matches the password.
     *
     * @param \Cake\Validation\Validator $validator The validator instance to which rules will be added.
     * @return \Cake\Validation\Validator The modified validator instance with the added rules.
     */
    public function validationResetPassword(Validator $validator): Validator
    {
        $validator
            ->add('password', [
                'length' => [
                    'rule' => ['minLength', 8],
                    'message' => 'Password must be at least 8 characters long.',
                ],
            ])
            ->add('password_confirm', [
                'compare' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => 'Passwords do not match.',
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
        if (!$entity->isNew() && $entity->isDirty('picture')) {
            $originalFilePath = $entity->getOriginal('picture');
            $fullOriginalFilePath = WWW_ROOT . 'files/Users/picture/' . $originalFilePath;
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

    /**
     * Custom finder method to retrieve only enabled records.
     *
     * This method modifies the query to filter out any records where the 'is_disabled'
     * field is set to 1, effectively returning only those records that are enabled.
     *
     * @param \Cake\ORM\Query $query The query object to modify.
     * @param array $options An array of options that can be used to customize the query.
     * @return \Cake\ORM\Query The modified query object with the 'is_disabled' condition applied.
     */
    public function findAuth(Query $query, array $options): Query
    {
        return $query->where(['is_disabled' => 0]);
    }
}
