<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Anthropic\TextSummaryGenerator;
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
    public static bool $shouldBeUnique = true;

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
            sprintf('Received article summary update message: %s : %s', $id, $title),
            'info',
            ['group_name' => 'App\Job\ArticleSummaryUpdateJob']
        );

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $article = $articlesTable->get($id);

        try {
            $summaryResult = $this->summaryGenerator->generateTextSummary(
                'article',
                (string)strip_tags($article->body)
            );
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Article Summary update failed. ID: %s Title: %s Error: %s',
                    $id,
                    $title,
                    $e->getMessage(),
                ),
                'error',
                ['group_name' => 'App\Job\ArticleSummaryUpdateJob']
            );

            return Processor::REJECT;
        }

        if (isset($summaryResult['summary'])) {

            if (empty($article->summary)) {
                $article->summary = $summaryResult['summary'];
            }

            if (empty($article->lead)) {
                $article->lead = $summaryResult['lead'];
            }

            // Save the data
            if ($articlesTable->save($article, ['noMessage' => true])) {
                $this->log(
                    sprintf('Article summary update completed successfully. Article ID: %s Title: %s', $id, $title),
                    'info',
                    ['group_name' => 'App\Job\ArticleSummaryUpdateJob']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    sprintf(
                        'Failed to save article summary updates. Article ID: %s Title: %s Error: %s',
                        $id,
                        $title,
                        json_encode($article->getErrors()),
                    ),
                    'error',
                    ['group_name' => 'App\Job\ArticleSummaryUpdateJob']
                );
            }
        } else {
            $this->log(
                sprintf(
                    'Article summary update failed. No valid result returned. Article ID: %s Title: %s',
                    $id,
                    $title
                ),
                'error',
                ['group_name' => 'App\Job\ArticleSummaryUpdateJob']
            );
        }

        return Processor::REJECT;
    }
}
