<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AiService;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ArticleSeoUpdateJob
 *
 * This job is responsible for updating the SEO metadata of an article using AI.
 * It processes messages from the queue to update various SEO-related fields of an article.
 */
class ArticleSeoUpdateJob extends AbstractJob
{
    /**
     * Instance of the AI service.
     *
     * @var \App\Service\Api\AiService
     */
    private AiService $aiService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\AiService|null $aiService
     */
    public function __construct(?AiService $aiService = null)
    {
        $this->aiService = $aiService ?? new AiService();
    }

    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'article SEO update';
    }

    /**
     * Executes the job to update article SEO metadata.
     *
     * @param \Cake\Queue\Job\Message $message The message containing article data
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        if (!$this->validateArguments($message, ['id', 'title'])) {
            return Processor::REJECT;
        }

        return $this->executeWithErrorHandling($id, function () use ($id, $title) {
            $articlesTable = $this->getTable('Articles');
            $article = $articlesTable->get($id);

            $seoResult = $this->aiService->generateArticleSeo(
                (string)$title,
                (string)strip_tags($article->body),
            );

            if ($seoResult) {
                $emptyFields = $articlesTable->emptySeoFields($article);
                array_map(fn($field) => $article->{$field} = $seoResult[$field], $emptyFields);

                return $articlesTable->save($article, ['noMessage' => true]);
            }

            return false;
        }, $title);
    }
}
