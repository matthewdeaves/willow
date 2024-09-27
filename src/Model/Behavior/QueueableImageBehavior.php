<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Service\RabbitMQService;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

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
     * Handles the after save event for an entity.
     *
     * This method is triggered after an entity is successfully saved in the database.
     * It performs the following actions:
     * 1) Checks if the specified field in the entity is marked as dirty (i.e., has been modified).
     * 2) If the field is dirty, it constructs a message containing the path to the saved image.
     * 3) Sends the constructed message to a RabbitMQ queue named 'image_uploads'. This message is
     * used to initiate the creation of resized image versions via the src/Command/ResizeImageConsumerCommand.php.
     *
     * @param \Cake\Event\EventInterface $event The event that was triggered after the entity was saved.
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved and potentially modified.
     * @param \ArrayObject $options Additional options that may influence the event handling.
     * @return void This method does not return any value.
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $config = $this->getConfig();
        if ($entity->isDirty($config['field'])) {
            $rabbitMQService = new RabbitMQService();
            $message = json_encode([
                'path' => WWW_ROOT . $config['folder_path'] . $entity->{$config['field']},
            ]);

            $rabbitMQService->sendMessage('image_uploads', $message);
        }
    }
}
