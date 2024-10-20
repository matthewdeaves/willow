<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Cake\Utility\Text;
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
    public static bool $shouldBeUnique = true;

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
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            __('Received article SEO update message: {0} : {1}', [$id, $title]),
            'info',
            ['group_name' => 'article_seo_update']
        );

        try {
            $articlesTable = TableRegistry::getTableLocator()->get('Articles');
            $article = $articlesTable->get($id);

            $seoResult = $this->anthropicService->generateArticleSeo($title, strip_tags($article->body));

            if ($seoResult) {

                //Set the data we got back
                $article->meta_title = $seoResult['meta_title'];
                $article->meta_description = $seoResult['meta_description'];
                $article->meta_keywords = $seoResult['meta_keywords'];
                $article->facebook_description = $seoResult['facebook_description'];
                $article->linkedin_description = $seoResult['linkedin_description'];
                $article->twitter_description = $seoResult['twitter_description'];
                $article->instagram_description = $seoResult['instagram_description'];

                // Save the data
                if ($articlesTable->save($article)) {
                    $this->log(
                        __('Article SEO update completed successfully. Article ID: {0} Title: {1}', [$id, $title]),
                        'info',
                        ['group_name' => 'article_seo_update']
                    );

                    return Processor::ACK;
                } else {
                    $this->log(
                        __('Failed to save article SEO updates. Article ID: {0} Title: {1}', [$id, $title]),
                        'error',
                        ['group_name' => 'article_seo_update']
                    );

                    return Processor::REJECT;
                }
            } else {
                $this->log(
                    __('Article SEO update failed. No result returned. Article ID: {0} Title: {1}', [$id, $title]),
                    'error',
                    ['group_name' => 'article_seo_update']
                );

                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->log(
                __('Unexpected error during article SEO update. Article ID: {0} Title: {1} Error: {2}', [
                    $id,
                    $title,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'article_seo_update']
            );

            return Processor::REJECT;
        }
    }
}
