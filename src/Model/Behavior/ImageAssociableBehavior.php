<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use ArrayObject;

class ImageAssociableBehavior extends Behavior
{

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->_table->belongsToMany('Images', [
            'foreignKey' => 'foreign_key',
            'targetForeignKey' => 'image_id',
            'joinTable' => 'models_images',
            'conditions' => ['ModelsImages.model' => $this->_table->getAlias()],
        ]);
    }

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!empty($entity->imageUploads)) {
            $this->saveImages($entity);
        }

        if (!empty($entity->unlinkedImages)) {
            $this->unlinkImages($entity, $entity->unlinkedImages);
        }
    }

    protected function saveImages(EntityInterface $entity): void
    {
        $imagesTable = TableRegistry::getTableLocator()->get('Images');
        $modelsImagesTable = TableRegistry::getTableLocator()->get('ModelsImages');

        foreach ($entity->imageUploads as $image) {
            $imageEntity = $imagesTable->newEntity([
                'image_file' => $image,
                'name' => $image->getClientFilename(),
            ]);
            if ($imagesTable->save($imageEntity)) {
                $modelsImagesTable->save($modelsImagesTable->newEntity([
                    'model' => $this->_table->getAlias(),
                    'foreign_key' => $entity->id,
                    'image_id' => $imageEntity->id,
                ]));
            }
        }
    }

    public function unlinkImages(EntityInterface $entity, array $imageIds): void
    {
        $imageIds = array_filter($imageIds, function($value) {
            return $value !== '0';
        });

        if (empty($imageIds)) {
            return;
        }

        $modelsImagesTable = TableRegistry::getTableLocator()->get('ModelsImages');
        
        foreach ($imageIds as $imageId) {
            $modelsImagesTable->deleteAll([
                'model' => $this->_table->getAlias(),
                'foreign_key' => $entity->id,
                'image_id' => $imageId
            ]);
        }
    }
}