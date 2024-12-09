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
 * It processes uploaded images asynchronously and optionally performs AI analysis.
 */
class QueueableImageBehavior extends Behavior
{
    /**
     * Default configuration for the QueueableImageBehavior.
     *
     * Configuration options:
     * - folder_path: (string) The base path where uploaded images will be stored
     * - field: (string) The entity field name that contains the uploaded file
     * - uploadConfig: (array) Configuration for the Upload behavior
     *   - fields: (array) Mapping of database fields to file attributes
     *     - dir: (string) Field storing the directory path
     *     - size: (string) Field storing the file size
     *     - type: (string) Field storing the MIME type
     *   - nameCallback: (callable|null) Function to generate unique filenames
     *   - deleteCallback: (callable|null) Function to handle file deletion
     *   - keepFilesOnDelete: (bool) Whether to retain files when entity is deleted
     *
     * Example usage:
     * ```
     * $this->addBehavior('QueueableImage', [
     *     'folder_path' => 'img/uploads/',
     *     'field' => 'profile_image'
     * ]);
     * ```
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'folder_path' => '',
        'field' => 'image',
        'uploadConfig' => [
            'fields' => [
                'dir' => 'dir',
                'size' => 'size',
                'type' => 'mime',
            ],
            'nameCallback' => null,
            'deleteCallback' => null,
            'keepFilesOnDelete' => false,
        ],
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

        // Set default callbacks if not provided
        if (empty($this->_config['uploadConfig']['nameCallback'])) {
            $this->_config['uploadConfig']['nameCallback'] = function ($table, $entity, $data, $field, $settings) {
                $file = $entity->{$field};
                $clientFilename = $file->getClientFilename();
                $ext = pathinfo($clientFilename, PATHINFO_EXTENSION);

                return Text::uuid() . '.' . strtolower($ext);
            };
        }

        if (empty($this->_config['uploadConfig']['deleteCallback'])) {
            $this->_config['uploadConfig']['deleteCallback'] = function ($path, $entity, $field, $settings) {
                $paths = [
                    $path . $entity->{$field},
                ];

                foreach (SettingsManager::read('ImageSizes') as $width) {
                    $paths[] = $path . $width . DS . $entity->{$field};
                }

                return $paths;
            };
        }

        // Prepare Upload behavior configuration
        $uploadConfig = [
            $this->getConfig('field') => array_merge(
                $this->_defaultConfig['uploadConfig'],
                $config['uploadConfig'] ?? []
            ),
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
     * It performs the following actions:
     * 1) Checks if the specified field in the entity is marked as dirty (i.e., has been modified).
     * 2) If the field is dirty, it constructs a message containing the path to the saved image.
     * 3) Enqueues two jobs using CakePHP's QueueManager:
     *    a) An image processing job (ProcessImageJob)
     *    b) An image analysis job (ImageAnalysisJob)
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered after the entity was saved.
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved and potentially modified.
     * @param \ArrayObject $options Additional options that may influence the event handling.
     * @return void This method does not return any value.
     * @throws \Exception If there's an error while pushing the jobs to the queue. The exception is caught and logged.
     * @uses \Queue\QueueManager::push() To add the image processing and analysis jobs to the queue.
     * @uses \Cake\Log\Log::error() To log any errors that occur during the queueing process.
     * @see \App\Job\ProcessImageJob The job class that will handle the image processing.
     * @see \App\Job\ImageAnalysisJob The job class that will handle the image analysis.
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
