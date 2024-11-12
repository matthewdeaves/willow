<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use Cake\Log\LogTrait;
use InvalidArgumentException;

/**
 * Class TextSummaryGenerator
 *
 * This class is responsible for generating summaries of text using the Anthropic
 * API service. It interacts with the AI prompts table to retrieve prompt data
 * and uses the AnthropicApiService to send requests and parse responses.
 */
class TextSummaryGenerator
{
    use LogTrait;

    /**
     * The Anthropic API service used for sending requests and parsing responses.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $apiService;

    /**
     * The AI prompts table used for retrieving prompt data.
     *
     * @var \App\Model\Table\AipromptsTable
     */
    private AipromptsTable $aipromptsTable;

    /**
     * Constructor for the TextSummaryGenerator class.
     *
     * @param \App\Service\Api\AnthropicApiService $apiService The API service for handling requests.
     * @param \App\Model\Table\AipromptsTable $aipromptsTable The table for AI prompts.
     */
    public function __construct(AnthropicApiService $apiService, AipromptsTable $aipromptsTable)
    {
        $this->apiService = $apiService;
        $this->aipromptsTable = $aipromptsTable;
    }

    /**
     * Generates a text summary using the Anthropic API.
     *
     * @param string $context The context for the text summary.
     * @param string $text The text to be summarized.
     * @return array The generated summary and key points.
     */
    public function generateTextSummary(string $context, string $text): array
    {
        $promptData = $this->getPromptData('text_summary');
        $payload = $this->createPayload($promptData, [
            'context' => $context,
            'text' => $text,
        ]);

        $response = $this->apiService->sendRequest($payload);
        $result = $this->apiService->parseResponse($response);

        return $this->ensureExpectedKeys($result);
    }

    /**
     * Creates a payload for the API request using the provided prompt data and content.
     *
     * @param array $promptData The prompt data retrieved from the AI prompts table.
     * @param array $content The content to be included in the payload.
     * @return array The created payload for the API request.
     */
    private function createPayload(array $promptData, array $content): array
    {
        return [
            'model' => $promptData['model'],
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => $promptData['temperature'],
            'system' => $promptData['system_prompt'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => json_encode($content),
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

    /**
     * Ensures that the result contains all expected keys, initializing them if necessary.
     *
     * @param array $result The result array to check and modify.
     * @return array The result array with all expected SEO keys initialized.
     */
    private function ensureExpectedKeys(array $result): array
    {
        $expectedKeys = [
            'summary',
            'key_points',
            'lead',
        ];

        foreach ($expectedKeys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = '';
                $this->log(
                    sprintf('Text Summary Generator did not find expected key: %s', $key),
                    'error',
                    ['group_name' => 'anthropic']
                );
            }
        }

        return $result;
    }
}
