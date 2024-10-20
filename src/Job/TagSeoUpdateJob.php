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

class TagSeoUpdateJob implements JobInterface
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
     * Constructor for TagSeoUpdateJob.
     */
    public function __construct()
    {
        $this->anthropicService = new AnthropicApiService();
    }

    public function execute(Message $message): ?string
    {
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            __('Received tag SEO update message: ID: {0} Title: {1}', [$id, $title]),
            'info',
            ['group_name' => 'tag_seo_update']
        );

        try {
            $tagsTable = TableRegistry::getTableLocator()->get('Tags');
            $tag = $tagsTable->get($id);

            $seoResult = $this->anthropicService->generateTagSeo($title, $tag->description);

            if ($seoResult) {
                //Set the data we got back
                $tag->meta_title = $seoResult['meta_title'];
                $tag->meta_description = $seoResult['meta_description'];
                $tag->meta_keywords = $seoResult['meta_keywords'];
                $tag->facebook_description = $seoResult['facebook_description'];
                $tag->linkedin_description = $seoResult['linkedin_description'];
                $tag->twitter_description = $seoResult['twitter_description'];
                $tag->instagram_description = $seoResult['instagram_description'];

                if ($tagsTable->save($tag)) {
                    $this->log(
                        __('Tag SEO update completed successfully. ID: {0} Title: {1}', [$id, $title]),
                        'info',
                        ['group_name' => 'tag_seo_update']
                    );
                    return Processor::ACK;
                } else {
                    $this->log(
                        __('Failed to save tag SEO updates. ID: {0} Title: {1}', [$id, $title]),
                        'error',
                        ['group_name' => 'tag_seo_update']
                    );
                }
            } else {
                $this->log(
                    __('Tag SEO update failed. No result returned. ID: {0} Title: {1}', [$id, $title]),
                    'error',
                    ['group_name' => 'tag_seo_update']
                );
            }
        } catch (Exception $e) {
            $this->log(
                __('Unexpected error during tag SEO update. ID: {0}, Title: {1} Error: {2}', [
                    $id,
                    $title,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'tag_seo_update']
            );
        }

        return Processor::REJECT;
    }
}
