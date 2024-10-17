<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\Database\Exception\DatabaseException;
use Cake\Http\Exception\HttpException;
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
     * Execute the tag SEO update job.
     *
     * @param \Cake\Queue\Job\Message $message The job message.
     * @return string|null The result of the job execution.
     */
    public function execute(Message $message): ?string
    {
        $args = $message->getArgument('args');
        $this->log(
            __('Received tag SEO update message: {0}', [json_encode($args)]),
            'debug',
            ['group_name' => 'tag_seo_update']
        );

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log(
                __('Invalid argument structure for tag SEO update job. Expected array, got: {0}', [gettype($args)]),
                'error',
                ['group_name' => 'tag_seo_update']
            );

            return Processor::REJECT;
        }

        $payload = $args[0];
        $tagId = $payload['id'] ?? null;
        $tagTitle = $payload['title'] ?? '';

        if (!$tagId) {
            $this->log(
                __('Missing required fields in tag SEO update payload. ID: {0}', [$tagId]),
                'error',
                ['group_name' => 'tag_seo_update']
            );

            return Processor::REJECT;
        }

        try {
            $tagsTable = TableRegistry::getTableLocator()->get('Tags');
            $tag = $tagsTable->get($tagId);
            $tagDescription = $tag->description ?? '';

            $seoResult = $this->anthropicService->generateTagSeo($tagTitle, $tagDescription);
            $seoFields = [
                'meta_title',
                'meta_description',
                'meta_keywords',
                'facebook_description',
                'linkedin_description',
                'twitter_description',
                'instagram_description',
            ];

            if ($seoResult) {
                $isUpdated = false;
                foreach ($seoFields as $field) {
                    if (isset($seoResult[$field]) && $tag->$field !== $seoResult[$field]) {
                        $tag->$field = $seoResult[$field];
                        $isUpdated = true;
                    }
                }

                if ($isUpdated) {
                    if ($tagsTable->save($tag)) {
                        $this->log(
                            __('Tag SEO update completed successfully. Tag ID: {0}', [$tagId]),
                            'info',
                            ['group_name' => 'tag_seo_update']
                        );
                        $this->log(
                            __('Acknowledging message for Tag ID: {0}', [$tagId]),
                            'debug',
                            ['group_name' => 'tag_seo_update']
                        );

                        return Processor::ACK;
                    } else {
                        $this->log(
                            __('Failed to save tag SEO updates. Tag ID: {0}', [$tagId]),
                            'error',
                            ['group_name' => 'tag_seo_update']
                        );

                        return Processor::REJECT;
                    }
                } else {
                    $this->log(
                        __('No changes detected for Tag ID: {0}. Acknowledging message.', [$tagId]),
                        'info',
                        ['group_name' => 'tag_seo_update']
                    );

                    return Processor::ACK;
                }
            } else {
                $this->log(
                    __('Tag SEO update failed. No result returned. Tag ID: {0}', [$tagId]),
                    'error',
                    ['group_name' => 'tag_seo_update']
                );

                return Processor::REJECT;
            }
        } catch (DatabaseException $e) {
            $this->log(
                __('Database error during tag SEO update. Tag ID: {0}, Error: {1}', [
                    $tagId,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'tag_seo_update']
            );

            return Processor::REJECT;
        } catch (HttpException $e) {
            $this->log(
                __('HTTP error during tag SEO update. Tag ID: {0}, Error: {1}', [
                    $tagId,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'tag_seo_update']
            );

            return Processor::REJECT;
        } catch (Exception $e) {
            $this->log(
                __('Unexpected error during tag SEO update. Tag ID: {0}, Error: {1}', [
                    $tagId,
                    $e->getMessage(),
                ]),
                'error',
                ['group_name' => 'tag_seo_update']
            );
            throw $e; // Rethrow unexpected exceptions
        }
    }
}
