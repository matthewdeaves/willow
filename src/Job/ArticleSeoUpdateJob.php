<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ArticleSeoUpdateJob
 *
 * This job is responsible for updating the SEO metadata of an article using the Anthropic API.
 * It processes messages from the queue to update various SEO-related fields of an article.
 */
class ArticleSeoUpdateJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts for the job.
     *
     * @var int|null
     */
    public static ?int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time.
     *
     * @var bool
     */
    public static bool $shouldBeUnique = true;

    /**
     * Instance of the Anthropic API service.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\AnthropicApiService|null $anthropicService
     */
    public function __construct(?AnthropicApiService $anthropicService = null)
    {
        $this->anthropicService = $anthropicService ?? new AnthropicApiService();
    }

    /**
     * Executes the job to update article SEO metadata.
     *
     * This method processes the message, retrieves the article, generates SEO content
     * using the Anthropic API, and updates the article with the new SEO metadata.
     *
     * @param \Cake\Queue\Job\Message $message The message containing article data.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        $this->anthropicService = new AnthropicApiService();

        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            sprintf('Received article SEO update message: %s : %s', $id, $title),
            'info',
            ['group_name' => 'App\Job\ArticleSeoUpdateJob']
        );

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $article = $articlesTable->get($id);

        try {
            $seoResult = $this->anthropicService->generateArticleSeo(
                (string)$title,
                (string)strip_tags($article->body)
            );
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Article SEO update failed. ID: %s Title: %s Error: %s',
                    $id,
                    $title,
                    $e->getMessage(),
                ),
                'error',
                ['group_name' => 'App\Job\ArticleSeoUpdateJob']
            );

            return Processor::REJECT;
        }

        if ($seoResult) {
            $emptyFields = $articlesTable->emptySeoFields($article);
            array_map(fn ($field) => $article->{$field} = $seoResult[$field], $emptyFields);

            if ($articlesTable->save($article, ['noMessage' => true])) {
                $this->log(
                    sprintf('Article SEO update completed successfully. Article ID: %s Title: %s', $id, $title),
                    'info',
                    ['group_name' => 'App\Job\ArticleSeoUpdateJob']
                );

                Cache::clear('articles');

                return Processor::ACK;
            } else {
                $this->log(
                    sprintf(
                        'Failed to save article SEO updates. Article ID: %s Title: %s Error: %s',
                        $id,
                        $title,
                        json_encode($article->getErrors()),
                    ),
                    'error',
                    ['group_name' => 'App\Job\ArticleSeoUpdateJob']
                );
            }
        } else {
            $this->log(
                sprintf('Article SEO update failed. No result returned. Article ID: %s Title: %s', $id, $title),
                'error',
                ['group_name' => 'App\Job\ArticleSeoUpdateJob']
            );
        }

        return Processor::REJECT;
    }
}
