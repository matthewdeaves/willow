<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Google\GoogleApiService;
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
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\AnthropicApiService|null $anthropicService
     */
    public function __construct(?GoogleApiService $googleService = null)
    {
        $this->apiService = $googleService ?? new GoogleApiService();
    }


    public function execute(Message $message): ?string
    {
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            sprintf('Received article translation message: %s : %s', $id, $title),
            'info',
            ['group_name' => 'article_translation']
        );

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $article = $articlesTable->get($id);

        $result = $this->apiService->translateArticle($article->title, $article->body);

        if ($result) {

            foreach ($result as $locale => $translation) {
                $article->translation($locale)->title = $translation['title'];
                $article->translation($locale)->body = $translation['body'];

                if ($articlesTable->save($article)) {
                    $this->log(
                        sprintf('Article translation completed successfully. Locale: %s Article ID: %s Title: %s', $locale, $id, $title),
                        'info',
                        ['group_name' => 'article_translation']
                    );
                } else {
                    $this->log(
                        sprintf('Failed to save article translation. Locale: %s Article ID: %s Title: %s', $locale, $id, $title),
                        'error',
                        ['group_name' => 'article_translation']
                    );
                }
            }

            return Processor::ACK;
        } else {
            $this->log(
                sprintf('Article translation failed. No result returned. Article ID: %s Title: %s', $id, $title),
                'error',
                ['group_name' => 'article_translation']
            );
        }

        return Processor::REJECT;
    }
}
