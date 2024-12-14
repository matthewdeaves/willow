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

/**
 * QueueableImageBehavior handles image upload processing and analysis through a queue system.
 *
 * This behavior automatically attaches the Upload behavior and configures it for image handling.
 * It processes uploaded images asynchronously and optionally queues a job to perform AI analysis.
 */
class QueueableImageBehavior extends Behavior
{
    /**
     * Default configuration for the QueueableImageBehavior.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'folder_path' => '',
        'field' => 'image',
    ];

    /**
     * Initializes the behavior.
     *
     * Sets up the Upload behavior with merged configuration settings and
     * configures the default callbacks if not overridden.
     *
     * @param array $config Configuration array for customizing both QueueableImage and Upload behaviors
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Get the field name
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

    /**
     * Handles the afterSave event for an entity.
     *
     * This method is triggered after an entity is successfully saved in the database.
     * It queues image processing and optional AI analysis jobs.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
     * @param \ArrayObject $options Additional options that may influence the event handling
     * @return void
     */
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
