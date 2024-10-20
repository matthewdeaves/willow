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
use Exception;
use Interop\Queue\Processor;

/**
 * CommentAnalysisJob Class
 *
 * This job is responsible for analyzing comments using the Anthropic API service.
 * It processes comments, checks for inappropriate content, and updates the comment status accordingly.
 */
class CommentAnalysisJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts to process the job
     *
     * @var int|null
     */
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
     * Constructor for CommentAnalysisJob.
     */
    public function __construct()
    {
        $this->anthropicService = new AnthropicApiService();
    }

    /**
     * Executes the comment analysis job
     *
     * This method processes the job message, analyzes the comment content,
     * and updates the comment status based on the analysis result.
     *
     * @param \Cake\Queue\Job\Message $message The job message containing comment data
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure, or Processor::REQUEUE on API overload
     */
    public function execute(Message $message): ?string
    {
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

        try {
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

                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->log(
                __('Error during comment analysis. Comment ID: {0}, Error: {1}', [$commentId, $e->getMessage()]),
                'error',
                ['group_name' => 'comment_analysis']
            );

            return Processor::REJECT;
        }
    }

    /**
     * Updates the comment status based on the analysis result
     *
     * This method updates the comment entity with the analysis results,
     * including whether it's inappropriate, the reason if so, and its display status.
     *
     * @param \App\Model\Entity\Comment $comment The comment entity to update
     * @param array $analysisResult The result of the comment analysis
     * @return void
     */
    private function updateCommentStatus(Comment $comment, array $analysisResult): void
    {
        $isInappropriate = $analysisResult['is_inappropriate'] ?? false;
        $reason = $analysisResult['reason'] ?? [];

        $comment->is_analyzed = true;
        $comment->display = !$isInappropriate; // Set display to false only if inappropriate
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
