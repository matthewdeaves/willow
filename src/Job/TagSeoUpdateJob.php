<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

class TagSeoUpdateJob implements JobInterface
{
    use LogTrait;

    public static int $maxAttempts = 3;

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
     * Executes the tag SEO update process for a given message.
     *
     * This method retrieves the tag information based on the provided ID and title from the message.
     * It then uses the anthropic service to generate SEO data for the tag. If the SEO data is successfully
     * generated and saved, it logs a success message and returns an acknowledgment. If the save operation
     * fails or no SEO data is returned, it logs an error message and returns a rejection.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the 'id' and 'title' arguments for the tag.
     * @return string|null Returns Processor::ACK if the operation is successful, Processor::REJECT otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If the tag with the given ID is not found.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If there's an error saving the tag data.
     * @uses \App\Model\Table\TagsTable
     * @uses \App\Service\Api\AnthropicApiService
     */
    public function execute(Message $message): ?string
    {
        $this->anthropicService = new AnthropicApiService();
        
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            __('Received tag SEO update message: ID: {0} Title: {1}', [$id, $title]),
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

        return Processor::REJECT;
    }
}
