<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
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
     * Executes the SEO update process for a given article.
     *
     * This method retrieves an article by its ID, generates SEO metadata using an external service,
     * and updates the article with the generated metadata. It logs the process and returns an
     * acknowledgment or rejection status based on the success of the operation.
     *
     * The method performs the following steps:
     * 1. Retrieves the article from the database using the provided ID.
     * 2. Calls an external service to generate SEO metadata based on the article's title and body.
     * 3. Updates the article with the generated SEO metadata, including:
     *    - Meta title
     *    - Meta description
     *    - Meta keywords
     *    - Facebook description
     *    - LinkedIn description
     *    - Twitter description
     *    - Instagram description
     * 4. Saves the updated article to the database.
     * 5. Logs the result of the operation.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the article ID and title.
     * @return string|null Returns Processor::ACK if the SEO update is successful and saved,
     *                     Processor::REJECT if the update fails or an error occurs.
     * @throws \Exception If an unexpected error occurs during the SEO update process.
     * @uses \App\Model\Table\ArticlesTable
     * @uses \App\Service\Api\AnthropicApiService
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
            }
        } else {
            $this->log(
                __('Article SEO update failed. No result returned. Article ID: {0} Title: {1}', [$id, $title]),
                'error',
                ['group_name' => 'article_seo_update']
            );
        }

        return Processor::REJECT;
    }
}
