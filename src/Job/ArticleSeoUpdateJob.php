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
            $expectedKeys = [
                'meta_title',
                'meta_description',
                'meta_keywords',
                'facebook_description',
                'linkedin_description',
                'twitter_description',
                'instagram_description',
            ];

            if ($seoResult) {
                // Just in case we don't get back the JSON keys we expect
                foreach ($expectedKeys as $key) {
                    if (isset($seoResult[$key])) {
                        $article->$key = $seoResult[$key];
                    }
                }

                // Sometimes meta_keywords comes back with ,'s
                // Replace commas with spaces and remove extra whitespace
                $article->meta_keywords = Text::cleanInsert($article->meta_keywords, ['clean' => true]);
                // Ensure only alphanumeric characters and spaces
                $article->meta_keywords = preg_replace('/[^a-zA-Z0-9\s]/', '', $article->meta_keyword);
                // Trim whitespace
                $article->meta_keywords = trim($article->meta_keywords);

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
