<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\AnthropicApiService;
use InvalidArgumentException;

/**
 * CommentAnalyzer Class
 *
 * This class is responsible for analyzing comments using the Anthropic API service.
 */
class CommentAnalyzer
{
    /**
     * @var \App\Service\Api\AnthropicApiService The Anthropic API service.
     */
    private AnthropicApiService $apiService;

    /**
     * @var \App\Model\Table\AipromptsTable The AI prompts table for retrieving prompt data.
     */
    private AipromptsTable $aipromptsTable;

    /**
     * CommentAnalyzer constructor.
     *
     * @param \App\Service\Api\AnthropicApiService $apiService The Anthropic API service.
     * @param \App\Model\Table\AipromptsTable $aipromptsTable The AI prompts table.
     */
    public function __construct(AnthropicApiService $apiService, AipromptsTable $aipromptsTable)
    {
        $this->apiService = $apiService;
        $this->aipromptsTable = $aipromptsTable;
    }

    /**
     * Analyzes a comment using the Anthropic API.
     *
     * @param string $comment The comment to be analyzed.
     * @return array The analysis results from the API.
     */
    public function analyze(string $comment): array
    {
        $promptData = $this->getPromptData('comment_analysis');
        $payload = $this->createPayload($promptData, $comment);

        $response = $this->apiService->sendRequest($payload);

        return $this->apiService->parseResponse($response);
    }

    /**
     * Creates a payload for the API request.
     *
     * @param array $promptData The prompt data retrieved from the AI prompts table.
     * @param string $comment The comment to be analyzed.
     * @return array The created payload.
     */
    private function createPayload(array $promptData, string $comment): array
    {
        return [
            'model' => $promptData['model'],
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => $promptData['temperature'],
            'system' => $promptData['system_prompt'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $comment,
                ],
            ],
        ];
    }

    /**
     * Retrieves prompt data for a specific task.
     *
     * @param string $task The task type for which to retrieve prompt data.
     * @return array The retrieved prompt data.
     * @throws \InvalidArgumentException If the task is unknown.
     */
    private function getPromptData(string $task): array
    {
        $prompt = $this->aipromptsTable->find()
            ->where(['task_type' => $task])
            ->first();

        if (!$prompt) {
            throw new InvalidArgumentException("Unknown task: {$task}");
        }

        return [
            'system_prompt' => $prompt->system_prompt,
            'model' => $prompt->model,
            'max_tokens' => $prompt->max_tokens,
            'temperature' => $prompt->temperature,
        ];
    }
}
