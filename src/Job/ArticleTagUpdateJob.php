<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ArticleTagUpdateJob
 *
 * This job is responsible for updating tags for articles using the Anthropic API.
 * It processes queued messages to update article tags based on the article's content.
 *
 * @package App\Job
 */
class ArticleTagUpdateJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts for this job.
     *
     * @var int|null
     */
    public static ?int $maxAttempts = 3;

    /**
     * Whether this job should be unique in the queue.
     *
     * @var bool
     */
    public static bool $shouldBeUnique = true;

    /**
     * The Anthropic API service used for generating article tags.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Constructor
     *
     * Initializes the Anthropic API service.
     */
    public function __construct()
    {
        $this->anthropicService = new AnthropicApiService();
    }

    /**
     * Executes the article tag update process.
     *
     * This method processes a message containing an article ID and title, retrieves the article and its associated tags,
     * and attempts to update the article's tags using an external service. It logs the process and handles any errors
     * that occur during execution.
     *
     * The method performs the following steps:
     * 1. Retrieves the article and all existing tags from the database.
     * 2. Calls an external service to generate new tags for the article.
     * 3. Creates new tags if they don't already exist in the database.
     * 4. Updates the article with the new tags.
     * 5. Saves the updated article to the database.
     *
     * Throughout the process, it logs various events and errors for monitoring and debugging purposes.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the article ID and title.
     * @return string|null Returns Processor::ACK if the update is successful, Processor::REJECT if it fails or an error occurs.
     * @throws \Exception If an unexpected error occurs during the process.
     * @uses \App\Model\Table\ArticlesTable
     * @uses \App\Model\Table\TagsTable
     * @uses \App\Service\Api\AnthropicApiService
     */
    public function execute(Message $message): ?string
    {
        // Get message data we need
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            __('Received article tag update message: {0} : {1}', [$id, $title]),
            'info',
            ['group_name' => 'article_tag_update']
        );

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');

        $article = $articlesTable->get($id, contain: ['Tags']);
        $allTags = $tagsTable->find()->select(['title'])->all()->extract('title')->toArray();

        $tagResult = $this->anthropicService->generateArticleTags($allTags, $article->title, $article->body);

        if ($tagResult && isset($tagResult['new_tags']) && is_array($tagResult['new_tags'])) {
            $newTags = [];
            foreach ($tagResult['new_tags'] as $tagTitle) {
                if (!in_array($tagTitle, $allTags)) {
                    $tag = $tagsTable->findOrCreate(['title' => $tagTitle, 'slug' => '']);
                    $newTags[] = $tag;

                    // Check if the tag was newly created & log
                    if ($tag->isNew()) {
                        $this->log(
                            __('New tag created: {0}', [$tagTitle]),
                            'info',
                            ['group_name' => 'article_tag_update']
                        );
                    }
                }
            }

            $article->tags = $newTags;

            if ($articlesTable->save($article)) {
                $this->log(
                    __('Article tag update completed successfully. Article ID: {0}', [$id]),
                    'info',
                    ['group_name' => 'article_tag_update']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    __('Failed to save article tag updates. Article ID: {0}', [$id]),
                    'error',
                    ['group_name' => 'article_tag_update']
                );
            }
        } else {
            $this->log(
                __('Article tag update failed. No valid result returned. Article ID: {0}', [$id]),
                'error',
                ['group_name' => 'article_tag_update']
            );
        }

        return Processor::REJECT;
    }
}
