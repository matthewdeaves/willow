<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Cake\ORM\Entity;
use Cake\ORM\Table;
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
    public static int $maxAttempts = 3;

    /**
     * Whether this job should be unique in the queue.
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

        $allTags = $tagsTable->getSimpleThreadedArray();

        try {
            $tagResult = $this->anthropicService->generateArticleTags(
                $allTags,
                (string)$article->title,
                (string)strip_tags($article->body),
            );
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Article Tag update failed. ID: %s Title: %s Error: %s',
                    $id,
                    $title,
                    $e->getMessage(),
                ),
                'error',
                ['group_name' => 'App\Job\ArticleTagUpdateJob']
            );

            return Processor::REJECT;
        }

        if (isset($tagResult['tags']) && is_array($tagResult['tags'])) {
            $newTags = [];
            foreach ($tagResult['tags'] as $rootTag) {
                $parentTag = $this->findOrSaveTag($tagsTable, $rootTag['tag'], $rootTag['description']);
                $newTags[] = $parentTag;
                if (isset($rootTag['children']) && is_array($rootTag['children'])) {
                    foreach ($rootTag['children'] as $childTag) {
                        $child = $this->findOrSaveTag(
                            $tagsTable,
                            $childTag['tag'],
                            $childTag['description'],
                            $parentTag->id
                        );
                        $newTags[] = $child;
                    }
                }
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
     * Finds an existing tag by title or creates a new one if it does not exist.
     *
     * This method searches for a tag in the provided tags table using the specified title.
     * If a tag with the given title is not found, it creates a new tag entity with the provided
     * title, description, and optional parent ID, and saves it to the database.
     *
     * @param \Cake\ORM\Table $tagsTable The table instance to search for or save the tag.
     * @param string $tagTitle The title of the tag to find or create.
     * @param string $tagDescription The description of the tag to create if it does not exist.
     * @param int|null $parentId The optional parent ID for the tag, default is null.
     * @return \Cake\ORM\Entity The found or newly created tag entity.
     */
    private function findOrSaveTag(
        Table $tagsTable,
        string $tagTitle,
        string $tagDescription,
        ?string $parentId = null
    ): Entity {
        $tag = $tagsTable->find()->where(['title' => $tagTitle])->first();
        if (!$tag) {
            $tag = $tagsTable->newEmptyEntity();
            $tag->title = $tagTitle;
            $tag->description = $tagDescription;
            $tag->slug = '';
            $tag->parent_id = $parentId;
            $tagsTable->save($tag);
        }

        return $tag;
    }
}
