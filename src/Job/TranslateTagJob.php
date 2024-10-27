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
    public static bool $shouldBeUnique = false;

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
            ['group_name' => 'tag_translation']
        );

        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $tag = $tagsTable->get($id);

        // Summary and SEO texts are populated by another Job, allow it to have completed before translating
        if (empty($tag->twitter_description)) {
            return Processor::REQUEUE;
        }

        $result = $this->apiService->translateTag(
            $tag->title,
            $tag->description,
            $tag->meta_title,
            $tag->meta_description,
            $tag->meta_keywords,
            $tag->facebook_description,
            $tag->linkedin_description,
            $tag->instagram_description,
            $tag->twitter_description,
        );

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

                if ($tagsTable->save($tag)) {
                    $this->log(
                        sprintf(
                            'Tag translation completed successfully. Locale: %s Tag ID: %s Title: %s',
                            $locale,
                            $id,
                            $title
                        ),
                        'info',
                        ['group_name' => 'tag_translation']
                    );
                } else {
                    $this->log(
                        sprintf(
                            'Failed to save Tag translation. Locale: %s Tag ID: %s Title: %s',
                            $locale,
                            $id,
                            $title
                        ),
                        'error',
                        ['group_name' => 'tag_translation']
                    );
                }
            }

            return Processor::ACK;
        } else {
            $this->log(
                sprintf('Tag translation failed. No result returned. Tag ID: %s Title: %s', $id, $title),
                'error',
                ['group_name' => 'tag_translation']
            );
        }

        return Processor::REJECT;
    }
}
