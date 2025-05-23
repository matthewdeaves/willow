<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\Queue\QueueManager;
use Cake\Utility\Text;

class QueueableImageBehavior extends Behavior
{
    protected array $_defaultConfig = [
        'folder_path' => '',
        'field' => 'image',
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $field = $this->getConfig('field');

        // Prepare Upload behavior configuration
        $uploadConfig = [
            $field => [
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
        ];

        // Add the Upload behavior if it's not already added
        if (!$this->_table->hasBehavior('Josegonzalez/Upload.Upload')) {
            $this->_table->addBehavior('Josegonzalez/Upload.Upload', $uploadConfig);
        }
    }

    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Only handle file deletion on updates when a new image is uploaded
        if (!$entity->isNew() && $entity->isDirty($this->getConfig('field'))) {
            $originalImage = $entity->getOriginal($this->getConfig('field'));
            
            if ($originalImage) {
                // Use a more reliable path construction
                $tableName = $this->_table->getTable();
                $field = $this->getConfig('field');
                $basePath = WWW_ROOT . 'files' . DS . ucfirst($tableName) . DS . $field . DS;
                
                // Delete the main file
                $mainFilePath = $basePath . $originalImage;
                if (file_exists($mainFilePath)) {
                    unlink($mainFilePath);
                }
                
                // Delete all resized versions
                foreach (SettingsManager::read('ImageSizes') as $width) {
                    $resizedPath = $basePath . $width . DS . $originalImage;
                    if (file_exists($resizedPath)) {
                        unlink($resizedPath);
                    }
                }
            }
        }
    }

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $config = $this->getConfig();
        if ($entity->isDirty($config['field'])) {
            $data = [
                'folder_path' => WWW_ROOT . $config['folder_path'],
                'file' => $entity->{$config['field']},
                'id' => $entity->id,
            ];

            // Queue up an image processing job
            QueueManager::push('App\Job\ProcessImageJob', $data);

            if (SettingsManager::read('AI.enabled')) {
                $data['model'] = $event->getSubject()->getAlias();

                if (SettingsManager::read('AI.imageAnalysis')) {
                    QueueManager::push('App\Job\ImageAnalysisJob', $data);
                }
            }
        }
    }
}