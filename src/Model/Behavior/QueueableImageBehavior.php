<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\Queue\QueueManager;

class QueueableImageBehavior extends Behavior
{
    /**
     * Default configuration for the QueueableImageBehavior.
     *
     * This array defines the default settings for the behavior, which can be
     * overridden when the behavior is attached to a table. It includes:
     *
     * - 'folder_path': The path to the folder where files will be stored.
     *                  Default is an empty string.
     * - 'field': The name of the field in the table that will be used for
     *            storing the file name. Default is an empty string.
     *
     * @var array<string, string>
     */
    protected array $_defaultConfig = [
        'folder_path' => '',
        'field' => '',
    ];

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
