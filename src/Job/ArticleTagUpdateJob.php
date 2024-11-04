<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Cake\Queue\QueueManager;
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
            sprintf('Received article tag update message: %s : %s', $id, $title),
            'info',
            ['group_name' => 'App\Job\ArticleTagUpdateJob']
        );

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');

        $article = $articlesTable->get(
            $id,
            fields: ['id', 'title', 'body'],
            contain: ['Tags' => ['fields' => ['id']]]
        );

        $allTags = $tagsTable->find()->select(['title'])->all()->extract('title')->toArray();

        $tagResult = $this->anthropicService->generateArticleTags(
            $allTags,
            (string)$article->title,
            (string)$article->body
        );

        if (isset($tagResult['tags']) && is_array($tagResult['tags'])) {
            $newTags = [];
            foreach ($tagResult['tags'] as $key => $tagTitle) {
                $tag = $tagsTable->find()->where(['title' => $tagTitle])->first();
                if (!$tag) {
                    $tag = $tagsTable->newEmptyEntity();
                    $tag->title = $tagTitle;
                    $tag->description = $tagResult['descriptions'][$key] ?? '';
                    $tag->slug = '';
                    $tagsTable->save($tag);
                }
                $newTags[] = $tag;
            }

            $article->tags = $newTags;

            if ($articlesTable->save($article, ['validate' => false, 'noMessage' => true])) {
                $this->log(
                    sprintf('Article tag update completed successfully. Article ID: %s', $id),
                    'info',
                    ['group_name' => 'App\Job\ArticleTagUpdateJob']
                );

                Cache::clear('articles');

                return Processor::ACK;
            } else {
                $this->log(
                    sprintf(
                        'Failed to save article tag updates. Article ID: %s Error: %s',
                        $id,
                        json_encode($article->getErrors())
                    ),
                    'error',
                    ['group_name' => 'App\Job\ArticleTagUpdateJob']
                );
            }
        } else {
            $this->log(
                sprintf('Article tag update failed. No valid result returned. Article ID: %s', $id),
                'error',
                ['group_name' => 'App\Job\ArticleTagUpdateJob']
            );
        }

        return Processor::REJECT;
    }

    /**
     * Queues a job with the provided job class and data.
     *
     * This method is used to queue jobs for various tasks related to tags, such as updating SEO fields
     * and translating tags. It uses the QueueManager to push the job into the queue and logs the queued
     * job with relevant information.
     *
     * @param string $job The fully qualified class name of the job to be queued.
     * @param array $data An associative array of data to be passed to the job. Typically includes:
     *                    - 'id' (int): The ID of the tag associated with the job.
     *                    - 'title' (string): The title of the tag.
     * @return void
     * @throws \Exception If there is an error while queueing the job.
     * @uses \Cake\Queue\QueueManager::push() Pushes the job into the queue.
     * @uses \Cake\Log\Log::info() Logs the queued job with relevant information.
     */
    private function queueJob(string $job, array $data): void
    {
        QueueManager::push($job, $data);
        $this->log(
            sprintf(
                'Queued a %s with data: %s',
                $job,
                json_encode($data),
            ),
            'info',
            ['group_name' => $job]
        );
    }
}
