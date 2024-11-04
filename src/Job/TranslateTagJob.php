<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Google\GoogleApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * TranslateTagJob class
 *
 * This job is responsible for translating tag data using the Google API service.
 * It retrieves the tag data from the database, translates the specified fields,
 * and saves the translated data back to the database.
 */
class TranslateTagJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts for the job.
     *
     * @var int|null
     */
    public static ?int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time.
     *
     * @var bool
     */
    public static bool $shouldBeUnique = true;

    /**
     * @var \App\Service\Api\Google\GoogleApiService The API service instance.
     */
    private GoogleApiService $apiService;

    /**
     * Constructor to allow dependency injection for testing.
     *
     * @param \App\Service\Api\Google\GoogleApiService|null $googleService The Google API service instance.
     */
    public function __construct(?GoogleApiService $googleService = null)
    {
        $this->apiService = $googleService ?? new GoogleApiService();
    }

    /**
     * Execute the job.
     *
     * @param \Cake\Queue\Job\Message $message The job message.
     * @return string|null The result of the job execution.
     */
    public function execute(Message $message): ?string
    {
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            sprintf('Received Tag translation message: %s : %s', $id, $title),
            'info',
            ['group_name' => 'App\Job\TranslateTagJob']
        );

        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $tag = $tagsTable->get($id);

        // If there are any empy fields to be translated, wait 10 seconds and requeue
        // there could be a job in the queue to generate those fields and in production
        // we have 3 queue consumers
        if (!empty($tagsTable->emptySeoFields($tag))) {
            sleep(10);

            return Processor::REQUEUE;
        }

        try {
            $result = $this->apiService->translateTag(
                (string)$tag->title,
                (string)$tag->description,
                (string)$tag->meta_title,
                (string)$tag->meta_description,
                (string)$tag->meta_keywords,
                (string)$tag->facebook_description,
                (string)$tag->linkedin_description,
                (string)$tag->instagram_description,
                (string)$tag->twitter_description,
            );
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Translate Tag Job failed. ID: %s Title: %s Error: %s',
                    $id,
                    $title,
                    $e->getMessage(),
                ),
                'error',
                ['group_name' => 'App\Job\TranslateTagJob']
            );

            return Processor::REJECT;
        }

        if ($result) {
            foreach ($result as $locale => $translation) {
                $tag->translation($locale)->title = $translation['title'];
                $tag->translation($locale)->description = $translation['description'];
                $tag->translation($locale)->meta_title = $translation['meta_title'];
                $tag->translation($locale)->meta_description = $translation['meta_description'];
                $tag->translation($locale)->meta_keywords = $translation['meta_keywords'];
                $tag->translation($locale)->facebook_description = $translation['facebook_description'];
                $tag->translation($locale)->linkedin_description = $translation['linkedin_description'];
                $tag->translation($locale)->instagram_description = $translation['instagram_description'];
                $tag->translation($locale)->twitter_description = $translation['twitter_description'];

                // Set flag to not trigger another message on save (would loop)
                if ($tagsTable->save($tag, ['noMessage' => true])) {
                    $this->log(
                        sprintf(
                            'Tag translation completed successfully. Locale: %s Tag ID: %s Title: %s',
                            $locale,
                            $id,
                            $title
                        ),
                        'info',
                        ['group_name' => 'App\Job\TranslateTagJob']
                    );
                } else {
                    $this->log(
                        sprintf(
                            'Failed to save Tag translation. Locale: %s Tag ID: %s Title: %s Error: %s',
                            $locale,
                            $id,
                            $title,
                            json_encode($tag->getErrors()),
                        ),
                        'error',
                        ['group_name' => 'App\Job\TranslateTagJob']
                    );
                }
            }

            return Processor::ACK;
        } else {
            $this->log(
                sprintf('Tag translation failed. No result returned. Tag ID: %s Title: %s', $id, $title),
                'error',
                ['group_name' => 'App\Job\TranslateTagJob']
            );
        }

        return Processor::REJECT;
    }
}
