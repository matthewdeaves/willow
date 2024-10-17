<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\Database\Exception\DatabaseException;
use Cake\Http\Exception\HttpException;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

class ArticleSeoUpdateJob implements JobInterface
{
    use LogTrait;

    public static ?int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time. (optional property)
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Constructor for ArticleSeoUpdateJob.
     */
    public function __construct()
    {
        $this->anthropicService = new AnthropicApiService();
    }

    /**
     * Execute the article SEO update job.
     *
     * @param \Cake\Queue\Job\Message $message The job message.
     * @return string|null The result of the job execution.
     */
    public function execute(Message $message): ?string
    {
        $args = $message->getArgument('args');
        $this->log(
            __('Received article SEO update message: {0}', [json_encode($args)]),
            'debug',
            ['group_name' => 'article_seo_update']
        );

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log(
                __('Invalid argument structure for article SEO update job. Expected array, got: {0}', [gettype($args)]),
                'error',
                ['group_name' => 'article_seo_update']
            );

            return Processor::REJECT;
        }

        $payload = $args[0];
        $articleId = $payload['id'] ?? null;
        $articleTitle = $payload['title'] ?? '';

        if (!$articleId) {
            $this->log(
                __('Missing required fields in article SEO update payload. ID: {0}', [$articleId]),
                'error',
                ['group_name' => 'article_seo_update']
            );

            return Processor::REJECT;
        }

        try {
            $articlesTable = TableRegistry::getTableLocator()->get('Articles');
            $article = $articlesTable->get($articleId);
            $articleBody = $article->body ?? '';

            $seoResult = $this->anthropicService->generateArticleSeo($articleTitle, $articleBody);
            $seoFields = [
                'meta_title',
                'meta_description',
                'meta_keywords',
                'facebook_description',
                'linkedin_description',
                'twitter_description',
                'instagram_description',
            ];

            if ($seoResult) {
                $isUpdated = false;
                foreach ($seoFields as $field) {
                    if (isset($seoResult[$field]) && $article->$field !== $seoResult[$field]) {
                        $article->$field = $seoResult[$field];
                        $isUpdated = true;
                    }
                }

                if ($isUpdated) {
                    if ($articlesTable->save($article)) {
                        $this->log(
                            __('Article SEO update completed successfully. Article ID: {0}', [$articleId]),
                            'info',
                            ['group_name' => 'article_seo_update']
                        );

                        return Processor::ACK;
                    } else {
                        $this->log(
                            __('Failed to save article SEO updates. Article ID: {0}', [$articleId]),
                            'error',
                            ['group_name' => 'article_seo_update']
                        );

                        return Processor::REJECT;
                    }
                } else {
                    $this->log(
                        __('No changes detected for Article ID: {0}. Acknowledging message.', [$articleId]),
                        'info',
                        ['group_name' => 'article_seo_update']
                    );

                    return Processor::ACK;
                }
            } else {
                $this->log(
                    __('Article SEO update failed. No result returned. Article ID: {0}', [$articleId]),
                    'error',
                    ['group_name' => 'article_seo_update']
                );

                return Processor::REJECT;
            }
        } catch (DatabaseException $e) {
            $this->log(
                __('Database error during article SEO update. Article ID: {0}, Error: {1}', [
                    $articleId,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'article_seo_update']
            );

            return Processor::REJECT;
        } catch (HttpException $e) {
            $this->log(
                __('HTTP error during article SEO update. Article ID: {0}, Error: {1}', [
                    $articleId,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'article_seo_update']
            );

            return Processor::REJECT;
        } catch (Exception $e) {
            $this->log(
                __('Unexpected error during article SEO update. Article ID: {0}, Error: {1}', [
                    $articleId,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'article_seo_update']
            );
            throw $e; // Rethrow unexpected exceptions
        }
    }
}
