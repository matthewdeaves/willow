<?php
declare(strict_types=1);

namespace App\Job;

use App\Model\Entity\Comment;
use App\Service\Api\AnthropicApiService;
use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * CommentAnalysisJob Class
 *
 * This job is responsible for analyzing comments using the Anthropic API service.
 * It processes comments from the queue, performs analysis, and updates the comment status
 * based on the analysis results.
 */
class CommentAnalysisJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts to process the job.
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
     * Instance of the Anthropic API service.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Executes the comment analysis job.
     *
     * This method performs the following steps:
     * 1. Initializes the Anthropic API service.
     * 2. Retrieves comment data from the message.
     * 3. Logs the receipt of the analysis message.
     * 4. Checks if the comment has already been analyzed.
     * 5. Performs the comment analysis using the Anthropic API service.
     * 6. Updates the comment status based on the analysis result.
     * 7. Logs the outcome of the analysis process.
     *
     * @param \Cake\Queue\Job\Message $message The message containing job data.
     * @return string|null The processor status (ACK or REJECT).
     */
    public function execute(Message $message): ?string
    {
        $this->anthropicService = new AnthropicApiService();

        // Get data we need
        $commentId = $message->getArgument('comment_id');
        $content = $message->getArgument('content');
        $userId = $message->getArgument('user_id');

        $this->log(
            __('Received comment analysis message: Comment ID: {0} User ID: {1}', [$commentId, $userId]),
            'info',
            ['group_name' => 'comment_analysis']
        );

        $commentsTable = TableRegistry::getTableLocator()->get('Comments');
        $comment = $commentsTable->get($commentId);

        if ($comment->is_analyzed) {
            $this->log(
                __('Comment already analyzed. Skipping. Comment ID: {0}', [$commentId]),
                'info',
                ['group_name' => 'comment_analysis']
            );

            return Processor::ACK;
        }

        $analysisResult = $this->anthropicService->analyzeComment($content);

        if ($analysisResult) {
            $this->updateCommentStatus($comment, $analysisResult);
            $this->log(
                __('Comment analysis completed successfully. Comment ID: {0}', [$commentId]),
                'info',
                ['group_name' => 'comment_analysis']
            );

            return Processor::ACK;
        } else {
            $this->log(
                __('Comment analysis failed. No result returned. Comment ID: {0}', [$commentId]),
                'error',
                ['group_name' => 'comment_analysis']
            );
        }

        return Processor::REJECT;
    }

    /**
     * Updates the comment status based on the analysis result.
     *
     * This method performs the following steps:
     * 1. Extracts the inappropriateness status and reason from the analysis result.
     * 2. Updates the comment entity with the analysis results.
     * 3. Saves the updated comment entity to the database.
     * 4. Clears the articles cache to reflect the updated comment status.
     *
     * @param \App\Model\Entity\Comment $comment The comment entity to update.
     * @param array $analysisResult The result of the comment analysis.
     * @return void
     */
    private function updateCommentStatus(Comment $comment, array $analysisResult): void
    {
        $isInappropriate = $analysisResult['is_inappropriate'] ?? false;
        $reason = $analysisResult['reason'] ?? [];

        $comment->is_analyzed = true;
        $comment->display = !$isInappropriate;
        $comment->is_inappropriate = $isInappropriate;
        $comment->inappropriate_reason = $isInappropriate ? json_encode($reason) : null;

        $commentsTable = TableRegistry::getTableLocator()->get('Comments');
        if (!$commentsTable->save($comment)) {
            $this->log(
                __('Failed to update comment status. Comment ID: {0}', [$comment->id]),
                'error',
                ['group_name' => 'comment_analysis']
            );
        }
        // Clear the cache
        Cache::clear('articles');
    }
}
