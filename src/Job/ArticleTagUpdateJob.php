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
        // Get message data we need
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            __('Received article tag update message: {0} : {1}', [$id, $title]),
            'info',
            ['group_name' => 'article_tag_update']
        );

        try {
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

                    return Processor::REJECT;
                }
            } else {
                $this->log(
                    __('Article tag update failed. No valid result returned. Article ID: {0}', [$id]),
                    'error',
                    ['group_name' => 'article_tag_update']
                );

                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->log(
                __('Unexpected error during article tag update. Article ID: {0}, Error: {1}', [
                    $id,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'article_tag_update']
            );

            return Processor::REJECT;
        }
    }
}
