<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Datasource\EntityInterface;

/**
 * TranslateTagJob Class
 *
 * Job for translating tag content using the Google Translate API.
 */
class TranslateTagJob extends AbstractTranslateJob
{
    /**
     * @inheritDoc
     */
    protected static function getJobType(): string
    {
        return 'tag translation';
    }

    /**
     * @inheritDoc
     */
    protected function getTableAlias(): string
    {
        return 'Tags';
    }

    /**
     * @inheritDoc
     */
    protected function getRequiredArguments(): array
    {
        return ['id', 'title'];
    }

    /**
     * @inheritDoc
     */
    protected function getDisplayNameArgument(): string
    {
        return 'title';
    }

    /**
     * @inheritDoc
     */
    protected function getEntityTypeName(): string
    {
        return 'Tag';
    }

    /**
     * @inheritDoc
     */
    protected function getFieldsForTranslation(EntityInterface $entity): array
    {
        return [
            'title' => (string)$entity->title,
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
