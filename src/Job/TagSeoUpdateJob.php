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

    /**
     * Executes the tag SEO update process.
     *
     * This method processes a message containing a tag ID and title, retrieves the corresponding tag from the database,
     * and uses an external service to generate SEO metadata for the tag. The generated metadata is then saved back to the
     * database. The method logs the progress and outcome of the operation, and returns a status code indicating success
     * or failure.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the tag ID and title.
     * @return string|null Returns Processor::ACK on successful update, Processor::REJECT on failure or error.
     * @throws \Exception If an unexpected error occurs during the process.
     * @uses \App\Model\Table\TagsTable
     * @uses \App\Service\Api\AnthropicApiService
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
