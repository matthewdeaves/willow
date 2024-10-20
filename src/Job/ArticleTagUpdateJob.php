<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

class ArticleTagUpdateJob implements JobInterface
{
    use LogTrait;

    public static ?int $maxAttempts = 3;

    public static bool $shouldBeUnique = true;

    private AnthropicApiService $anthropicService;

    public function __construct()
    {
        $this->anthropicService = new AnthropicApiService();
    }

    public function execute(Message $message): ?string
    {
        $args = $message->getArgument('args');
        $this->log(
            __('Received article tag update message: {0}', [json_encode($args)]),
            'debug',
            ['group_name' => 'article_tag_update']
        );

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log(
                __('Invalid argument structure for article tag update job. Expected array, got: {0}', [gettype($args)]),
                'error',
                ['group_name' => 'article_tag_update']
            );

            return Processor::REJECT;
        }

        $payload = $args[0];
        $articleId = $payload['id'] ?? null;

        if (!$articleId) {
            $this->log(
                __('Missing required fields in article tag update payload. ID: {0}', [$articleId]),
                'error',
                ['group_name' => 'article_tag_update']
            );

            return Processor::REJECT;
        }

        try {
            $articlesTable = TableRegistry::getTableLocator()->get('Articles');
            $tagsTable = TableRegistry::getTableLocator()->get('Tags');

            $article = $articlesTable->get($articleId, contain: ['Tags']);
            $allTags = $tagsTable->find()->select(['title'])->all()->extract('title')->toArray();

            debug($allTags);

            $tagResult = $this->anthropicService->generateArticleTags($allTags, $article->title, $article->body);
            debug($tagResult['new_tags']);

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
                        __('Article tag update completed successfully. Article ID: {0}', [$articleId]),
                        'info',
                        ['group_name' => 'article_tag_update']
                    );

                    return Processor::ACK;
                } else {
                    $this->log(
                        __('Failed to save article tag updates. Article ID: {0}', [$articleId]),
                        'error',
                        ['group_name' => 'article_tag_update']
                    );

                    return Processor::REJECT;
                }
            } else {
                $this->log(
                    __('Article tag update failed. No valid result returned. Article ID: {0}', [$articleId]),
                    'error',
                    ['group_name' => 'article_tag_update']
                );

                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->log(
                __('Unexpected error during article tag update. Article ID: {0}, Error: {1}', [
                    $articleId,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'article_tag_update']
            );
            throw $e; // Rethrow unexpected exceptions
        }
    }
}
