<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
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
    public static bool $shouldBeUnique = true;

    /**
     * The Anthropic API service used for generating SEO content.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Constructor to allow dependency injection for testing.
     *
     * @param \App\Service\Api\Anthropic\AnthropicApiService|null $anthropicService The Google API service instance.
     */
    public function __construct(?AnthropicApiService $anthropicService = null)
    {
        $this->anthropicService = $anthropicService ?? new AnthropicApiService();
    }

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
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            sprintf(
                'Received tag SEO update message: ID: %s Title: %s',
                $id,
                $title,
            ),
            'info',
            ['group_name' => 'App\Job\TagSeoUpdateJob'],
        );

        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $tag = $tagsTable->get($id);

        try {
            $seoResult = $this->anthropicService->generateTagSeo(
                (string)$title,
                (string)$tag->description,
            );
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Tag SEO update failed. ID: %s Title: %s Error: %s',
                    $id,
                    $title,
                    $e->getMessage(),
                ),
                'error',
                ['group_name' => 'App\Job\TagSeoUpdateJob'],
            );

            return Processor::REJECT;
        }

        if ($seoResult) {
            $emptyFields = $tagsTable->emptySeoFields($tag);
            array_map(fn($field) => $tag->{$field} = $seoResult[$field], $emptyFields);

            if ($tagsTable->save($tag, ['noMessage' => true])) {
                $this->log(
                    sprintf(
                        'Tag SEO update completed successfully. ID: %s Title: %s',
                        $id,
                        $title,
                    ),
                    'info',
                    ['group_name' => 'App\Job\TagSeoUpdateJob'],
                );

                Cache::clear('content');

                return Processor::ACK;
            } else {
                $this->log(
                    sprintf(
                        'Failed to save tag SEO updates. ID: %s Title: %s Errors %s',
                        $id,
                        $title,
                        json_encode($tag->getErrors()),
                    ),
                    'error',
                    ['group_name' => 'App\Job\TagSeoUpdateJob'],
                );
            }
        } else {
            $this->log(
                sprintf(
                    'Tag SEO update failed. No result returned. ID: %s Title: %s',
                    $id,
                    $title,
                ),
                'error',
                ['group_name' => 'App\Job\TagSeoUpdateJob'],
            );
        }

        return Processor::REJECT;
    }
}
