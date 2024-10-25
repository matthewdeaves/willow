<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\Anthropic\AnthropicApiService;
use InvalidArgumentException;

/**
 * CommentAnalyzer Class
 *
 * This class is responsible for analyzing comments using the Anthropic API service.
 * It interacts with the AI prompts table to retrieve prompt data and uses the AnthropicApiService
 * to send requests and parse responses for comment analysis.
 */
class CommentAnalyzer
{
    /**
     * The Anthropic API service used for sending requests and parsing responses.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $apiService;

    /**
     * The AI prompts table for retrieving prompt data necessary for comment analysis.
     *
     * @var \App\Model\Table\AipromptsTable
     */
    private AipromptsTable $aipromptsTable;

    /**
     * CommentAnalyzer constructor.
     *
     * Initializes the API service and AI prompts table for comment analysis.
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
     * This method retrieves the appropriate prompt data, creates a payload,
     * sends a request to the Anthropic API, and processes the response to analyze the comment.
     *
     * @param string $comment The comment to be analyzed.
     * @return array The analysis results from the API, containing various aspects of the comment analysis.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function analyze(string $comment): array
    {
        $promptData = $this->getPromptData('comment_analysis');
        $payload = $this->createPayload($promptData, $comment);

        $response = $this->apiService->sendRequest($payload);

        return $this->apiService->parseResponse($response);
    }

    /**
     * Creates a payload for the API request using the provided prompt data and comment.
     *
     * @param array $promptData The prompt data retrieved from the AI prompts table.
     * @param string $comment The comment to be analyzed.
     * @return array The created payload for the API request.
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
     * Retrieves prompt data for a specific task from the AI prompts table.
     *
     * @param string $task The task type for which to retrieve prompt data.
     * @return array The retrieved prompt data including system prompt, model, max tokens, and temperature.
     * @throws \InvalidArgumentException If the task is unknown or not found in the AI prompts table.
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
