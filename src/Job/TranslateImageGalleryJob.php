<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Datasource\EntityInterface;

/**
 * TranslateImageGalleryJob Class
 *
 * Job for translating image gallery content using the Google Translate API.
 */
class TranslateImageGalleryJob extends AbstractTranslateJob
{
    /**
     * @inheritDoc
     */
    protected static function getJobType(): string
    {
        return 'image gallery translation';
    }

    /**
     * @inheritDoc
     */
    protected function getTableAlias(): string
    {
        return 'ImageGalleries';
    }

    /**
     * @inheritDoc
     */
    protected function getRequiredArguments(): array
    {
        return ['id', 'name'];
    }

    /**
     * @inheritDoc
     */
    protected function getDisplayNameArgument(): string
    {
        return 'name';
    }

    /**
     * @inheritDoc
     */
    protected function getEntityTypeName(): string
    {
        return 'Gallery';
    }

    /**
     * @inheritDoc
     */
    protected function getFieldsForTranslation(EntityInterface $entity): array
    {
        return [
            'name' => (string)$entity->name,
            'description' => (string)$entity->description,
            'meta_title' => (string)$entity->meta_title,
            'meta_description' => (string)$entity->meta_description,
            'meta_keywords' => (string)$entity->meta_keywords,
            'facebook_description' => (string)$entity->facebook_description,
            'linkedin_description' => (string)$entity->linkedin_description,
            'instagram_description' => (string)$entity->instagram_description,
            'twitter_description' => (string)$entity->twitter_description,
        ];
    }
}
