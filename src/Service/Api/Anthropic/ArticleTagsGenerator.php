<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use Cake\Log\LogTrait;
use InvalidArgumentException;

/**
 * ArticleTagsGenerator
 *
 * This class is responsible for generating article tags using the Anthropic API service.
 * It interacts with the AI prompts table to retrieve prompt data and uses the AnthropicApiService
 * to send requests and parse responses.
 */
class ArticleTagsGenerator
{
    use LogTrait;

    /**
     * The Anthropic API service used for sending requests and parsing responses.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $apiService;

    /**
     * The AI prompts table for retrieving prompt data necessary for generating tags.
     *
     * @var \App\Model\Table\AipromptsTable
     */
    private AipromptsTable $aipromptsTable;

    /**
     * ArticleTagsGenerator constructor.
     *
     * Initializes the API service and AI prompts table.
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
     * Generates article tags based on the provided title and body content.
     *
     * This method retrieves the appropriate prompt data, creates a payload,
     * sends a request to the Anthropic API, and processes the response to generate tags.
     *
     * @param array $allTags An array of existing tags.
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated tags for the article.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function generateArticleTags(array $allTags, string $title, string $body): array
    {
        $promptData = $this->getPromptData('article_tag_generation');
        $payload = $this->createPayload($promptData, [
            'existing_tags' => $allTags,
            'article_title' => $title,
            'article_content' => $body,
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
     * @return array The retrieved prompt data.
     * @throws \InvalidArgumentException If the task is unknown or not found.
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
     * @return array The result array with all expected keys initialized.
     */
    private function ensureExpectedKeys(array $result): array
    {
        $expectedKeys = [
            'tags',
        ];

        foreach ($expectedKeys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = '';
                $this->log(
                    sprintf('Article Tag Generator did not find expected key: %s', $key),
                    'error',
                    ['group_name' => 'anthropic'],
                );
            }
        }

        return $result;
    }
}
