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
 * This job is responsible for updating the tags of an article using the Anthropic API.
 * It processes messages from the queue to generate and update tags for articles.
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
    public static bool $shouldBeUnique = false;

    /**
     * Instance of the Anthropic API service.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Executes the job to update article tags.
     *
     * This method processes the message, retrieves the article, generates new tags using the Anthropic API,
     * and updates the article with the new tags.
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
            __('Received article tag update message: {0} : {1}', [$id, $title]),
            'info',
            ['group_name' => 'article_tag_update']
        );

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');

        $article = $articlesTable->get(
            $id,
            fields: ['id', 'title', 'body'],
            contain: ['Tags' => ['fields' => ['id']]]
        );

        $allTags = $tagsTable->find()->select(['title'])->all()->extract('title')->toArray();

        $tagResult = $this->anthropicService->generateArticleTags($allTags, $article->title, $article->body);

        if ($tagResult && isset($tagResult['tags']) && is_array($tagResult['tags'])) {
            $newTags = [];
            foreach ($tagResult['tags'] as $tagTitle) {
                $tag = $tagsTable->findOrCreate(['title' => $tagTitle]);

                if ($tag->isNew()) {
                    $this->log(
                        __('New tag created: {0}', [$tagTitle]),
                        'info',
                        ['group_name' => 'article_tag_update']
                    );
                }
                $newTags[] = $tag;
            }

            $article->tags = $newTags;

            if ($articlesTable->save($article, ['validate' => false])) {
                $this->log(
                    __('Article tag update completed successfully. Article ID: {0}', [$id]),
                    'info',
                    ['group_name' => 'article_tag_update']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    __(
                        'Failed to save article tag updates. Article ID: {0} Error: {1}',
                        [
                            $id,
                            json_encode($article->getErrors()),
                        ]
                    ),
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
