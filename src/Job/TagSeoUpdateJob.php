<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * TagSeoUpdateJob
 *
 * This job is responsible for updating SEO-related information for tags using the Anthropic API.
 * It processes queued messages, retrieves tag information, generates SEO content, and updates the tag record.
 */
class TagSeoUpdateJob implements JobInterface
{
    use LogTrait;

    /**
     * The maximum number of attempts for this job.
     *
     * @var int
     */
    public static int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of this job on the queue at a time.
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * The Anthropic API service used for generating SEO content.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Executes the job to update tag SEO information.
     *
     * This method processes the queued message, retrieves the tag, generates SEO content using the Anthropic API,
     * and updates the tag record with the new SEO information.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the job data.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        $this->anthropicService = new AnthropicApiService();

        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            sprintf(
                'Received tag SEO update message: ID: %s Title: %s',
                $id,
                $title
            ),
            'info',
            ['group_name' => 'tag_seo_update']
        );

        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $tag = $tagsTable->get($id);

        // Convert null description to an empty string
        if ($tag->description === null) {
            $tag->description = '';
        }

        $seoResult = $this->anthropicService->generateTagSeo($title, $tag->description);

        if ($seoResult) {
            // Set the data we got back
            $tag->meta_title = $seoResult['meta_title'];
            $tag->meta_description = $seoResult['meta_description'];
            $tag->meta_keywords = $seoResult['meta_keywords'];
            $tag->facebook_description = $seoResult['facebook_description'];
            $tag->linkedin_description = $seoResult['linkedin_description'];
            $tag->twitter_description = $seoResult['twitter_description'];
            $tag->instagram_description = $seoResult['instagram_description'];
            $tag->description = !empty($seoResult['description']) ? $seoResult['description'] : $tag->description;

            if ($tagsTable->save($tag)) {
                $this->log(
                    sprintf(
                        'Tag SEO update completed successfully. ID: %s Title: %s',
                        $id,
                        $title
                    ),
                    'info',
                    ['group_name' => 'tag_seo_update']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    sprintf(
                        'Failed to save tag SEO updates. ID: %s Title: %s',
                        $id,
                        $title
                    ),
                    'error',
                    ['group_name' => 'tag_seo_update']
                );
            }
        } else {
            $this->log(
                sprintf(
                    'Tag SEO update failed. No result returned. ID: %s Title: %s',
                    $id,
                    $title
                ),
                'error',
                ['group_name' => 'tag_seo_update']
            );
        }

        return Processor::REJECT;
    }
}
