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

    /**
     * Execute the tag SEO update job.
     *
     * @param \Cake\Queue\Job\Message $message The job message.
     * @return string|null The result of the job execution.
     */
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
                        $tag->$key = $seoResult[$key];
                    }
                }

                // Sometimes meta_keywords comes back with ,'s
                // Replace commas with spaces and remove extra whitespace
                $tag->meta_keywords = Text::cleanInsert($tag->meta_keywords, ['clean' => true]);
                // Ensure only alphanumeric characters and spaces
                $tag->meta_keywords = preg_replace('/[^a-zA-Z0-9\s]/', '', $tag->meta_keyword);
                // Trim whitespace
                $tag->meta_keywords = trim($tag->meta_keywords);

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

                    return Processor::REJECT;
                }
            } else {
                $this->log(
                    __('Tag SEO update failed. No result returned. ID: {0} Title: {1}', [$id, $title]),
                    'error',
                    ['group_name' => 'tag_seo_update']
                );

                return Processor::REJECT;
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

            return Processor::REJECT;
        }
    }
}
