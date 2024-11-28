<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

class TranslateArticleJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts for the job.
     *
     * @var int|null
     */
    public static int $maxAttempts = 3;

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
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\AnthropicApiService|null $anthropicService
     */
    public function __construct(?GoogleApiService $googleService = null)
    {
        $this->apiService = $googleService ?? new GoogleApiService();
    }

    /**
     * Executes the article translation process based on the received message.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the article ID and title.
     * @return string|null The processing result:
     *                     - Processor::REQUEUE if the article summary is empty, indicating that the translation should be requeued.
     *                     - Processor::ACK if the article translation is completed successfully.
     *                     - Processor::REJECT if the article translation fails.
     *                     - null if an unexpected error occurs.
     */
    public function execute(Message $message): ?string
    {
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            sprintf('Received Article translation message: %s : %s', $id, $title),
            'info',
            ['group_name' => 'App\Job\TranslateArticleJob']
        );

        if (empty(array_filter(SettingsManager::read('Translations', [])))) {
            $this->log(
                sprintf(
                    'Received Article translation message but there are no languages enabled for translation: %s : %s',
                    $id,
                    $title
                ),
                'warning',
                ['group_name' => 'App\Job\TranslateArticleJob']
            );

            return Processor::REJECT;
        }

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $article = $articlesTable->get($id);

        // If there are any empy fields to be translated, wait 10 seconds and requeue
        // there could be a job in the queue to generate those fields and in production
        // we have 3 queue consumers
        // if (!empty($articlesTable->emptySeoFields($article))) {
        //     sleep(10);

        //     return Processor::REQUEUE;
        // }

        try {
            $result = $this->apiService->translateArticle(
                (string)$article->title,
                (string)$article->lede,
                (string)$article->body,
                (string)$article->summary,
                (string)$article->meta_title,
                (string)$article->meta_description,
                (string)$article->meta_keywords,
                (string)$article->facebook_description,
                (string)$article->linkedin_description,
                (string)$article->instagram_description,
                (string)$article->twitter_description,
            );
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Translate Article Job failed. ID: %s Title: %s Error: %s',
                    $id,
                    $title,
                    $e->getMessage(),
                ),
                'error',
                ['group_name' => 'App\Job\TranslateArticleJob']
            );

            return Processor::REJECT;
        }

        if ($result) {
            foreach ($result as $locale => $translation) {
                $article->translation($locale)->title = $translation['title'];
                $article->translation($locale)->lede = $translation['lede'];
                $article->translation($locale)->body = $translation['body'];
                $article->translation($locale)->summary = $translation['summary'];
                $article->translation($locale)->meta_title = $translation['meta_title'];
                $article->translation($locale)->meta_description = $translation['meta_description'];
                $article->translation($locale)->meta_keywords = $translation['meta_keywords'];
                $article->translation($locale)->facebook_description = $translation['facebook_description'];
                $article->translation($locale)->linkedin_description = $translation['linkedin_description'];
                $article->translation($locale)->instagram_description = $translation['instagram_description'];
                $article->translation($locale)->twitter_description = $translation['twitter_description'];

                if ($articlesTable->save($article, ['noMessage' => true])) {
                    $this->log(
                        sprintf(
                            'Article translation completed successfully. Locale: %s Article ID: %s Title: %s',
                            $locale,
                            $id,
                            $title
                        ),
                        'info',
                        ['group_name' => 'App\Job\TranslateArticleJob']
                    );
                } else {
                    $this->log(
                        sprintf(
                            'Failed to save article translation. Locale: %s Article ID: %s Title: %s Error: %s',
                            $locale,
                            $id,
                            $title,
                            json_encode($article->getErrors())
                        ),
                        'error',
                        ['group_name' => 'App\Job\TranslateArticleJob']
                    );
                }
            }
            Cache::clear('articles');

            return Processor::ACK;
        } else {
            $this->log(
                sprintf('Article translation failed. No result returned. Article ID: %s Title: %s', $id, $title),
                'error',
                ['group_name' => 'App\Job\TranslateArticleJob']
            );
        }

        return Processor::REJECT;
    }
}
