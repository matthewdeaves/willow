<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

class CommentAnalysisJob implements JobInterface
{
    use LogTrait;

    public static ?int $maxAttempts = 3;
    public static bool $shouldBeUnique = true;

    public function execute(Message $message): ?string
    {
        $args = $message->getArgument('args');
        $this->log(
            __('Received comment analysis message: {0}', [json_encode($args)]),
            'debug',
            ['group_name' => 'comment_analysis']
        );

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log(
                __('Invalid argument structure for comment analysis job. Expected array, got: {0}', [gettype($args)]),
                'error',
                ['group_name' => 'comment_analysis']
            );
            return Processor::REJECT;
        }

        $payload = $args[0];
        $commentId = $payload['comment_id'] ?? null;
        $userId = $payload['user_id'] ?? null;
        $content = $payload['content'] ?? null;

        if (!$commentId || !$userId || !$content) {
            $this->log(
                __('Missing required fields in comment analysis payload. Comment ID: {0}, User ID: {1}', [$commentId, $userId]),
                'error',
                ['group_name' => 'comment_analysis']
            );
            return Processor::REJECT;
        }

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
            $anthropicService = new AnthropicApiService();
            $analysisResult = $anthropicService->analyzeComment($content);

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
            
            // Check if it's an overloaded error
            if (strpos($e->getMessage(), 'Overloaded') !== false) {
                return Processor::REQUEUE;
            }
            
            return Processor::REJECT;
        }
    }

    private function updateCommentStatus($comment, array $analysisResult): void
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
    }
}