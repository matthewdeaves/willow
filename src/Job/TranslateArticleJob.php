<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Datasource\EntityInterface;

/**
 * TranslateArticleJob Class
 *
 * Job for translating article content using the Google Translate API.
 */
class TranslateArticleJob extends AbstractTranslateJob
{
    /**
     * @inheritDoc
     */
    protected static function getJobType(): string
    {
        return 'article translation';
    }

    /**
     * @inheritDoc
     */
    protected function getTableAlias(): string
    {
        return 'Articles';
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
        return 'Article';
    }

    /**
     * @inheritDoc
     */
    protected function getFieldsForTranslation(EntityInterface $entity): array
    {
        return [
            'title' => (string)$entity->title,
            'lede' => (string)$entity->lede,
            'body' => (string)$entity->body,
            'summary' => (string)$entity->summary,
            'meta_title' => (string)$entity->meta_title,
            'meta_description' => (string)$entity->meta_description,
            'meta_keywords' => (string)$entity->meta_keywords,
            'facebook_description' => (string)$entity->facebook_description,
            'linkedin_description' => (string)$entity->linkedin_description,
            'instagram_description' => (string)$entity->instagram_description,
            'twitter_description' => (string)$entity->twitter_description,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getHtmlFields(): array
    {
        return ['body'];
    }

    /**
     * @inheritDoc
     */
    protected function useHtmlFormat(): bool
    {
        return true;
    }
}
