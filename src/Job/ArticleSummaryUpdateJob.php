<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\TextSummaryGenerator;
use App\Service\Api\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ArticleSummaryUpdateJob
 *
 * This job is responsible for generating and updating the summary of an article using the TextSummaryGenerator.
 * It processes messages from the queue to update the summary field of an article.
 */
class ArticleSummaryUpdateJob implements JobInterface
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
     * Instance of the TextSummaryGenerator service.
     *
     * @var \App\Service\Api\Anthropic\TextSummaryGenerator
     */
    private TextSummaryGenerator $summaryGenerator;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\Anthropic\TextSummaryGenerator|null $summaryGenerator
     */
    public function __construct(?TextSummaryGenerator $summaryGenerator = null)
    {
        $this->summaryGenerator = $summaryGenerator ?? new TextSummaryGenerator(
            new AnthropicApiService(),
            TableRegistry::getTableLocator()->get('Aiprompts')
        );
    }

    /**
     * Executes the job to generate and update article summary.
     *
     * This method processes the message, retrieves the article, generates a summary
     * using the TextSummaryGenerator, and updates the article with the new summary.
     *
     * @param \Cake\Queue\Job\Message $message The message containing article data.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $this->log(
            __('Received article summary update message: {0} : {1}', [$id, $title]),
            'info',
            ['group_name' => 'article_summary_update']
        );

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $article = $articlesTable->get($id);

        $summaryResult = $this->summaryGenerator->generateTextSummary('article', strip_tags($article->body));

        if ($summaryResult && isset($summaryResult['summary'])) {
            // Set the summary data we got back
            $article->summary = $summaryResult['summary'];

            // We don't do anything with key points for now
            /*
            if (isset($summaryResult['key_points'])) {
                $article->key_points = json_encode($summaryResult['key_points']);
            }
            */

            // Save the data
            if ($articlesTable->save($article)) {
                $this->log(
                    __('Article summary update completed successfully. Article ID: {0} Title: {1}', [$id, $title]),
                    'info',
                    ['group_name' => 'article_summary_update']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    __('Failed to save article summary updates. Article ID: {0} Title: {1}', [$id, $title]),
                    'error',
                    ['group_name' => 'article_summary_update']
                );
            }
        } else {
            $this->log(
                __(
                    'Article summary update failed. No valid result returned. Article ID: {0} Title: {1}',
                    [$id, $title]
                ),
                'error',
                ['group_name' => 'article_summary_update']
            );
        }

        return Processor::REJECT;
    }
}
