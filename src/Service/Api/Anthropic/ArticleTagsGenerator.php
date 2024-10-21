<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\AnthropicApiService;
use InvalidArgumentException;

class ArticleTagsGenerator
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
     * SeoContentGenerator constructor.
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
     * Generates SEO content for a tag.
     *
     * @param string $tagTitle The title of the tag.
     * @param string $tagDescription The description of the tag.
     * @return array The generated SEO content.
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
     * Creates a payload for the API request.
     *
     * @param array $promptData The prompt data retrieved from the AI prompts table.
     * @param array $content The content to be included in the payload.
     * @return array The created payload.
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

    /**
     * Ensures that the result contains all expected keys.
     *
     * @param array $result The result array to check and modify.
     * @return array The result array with all expected keys.
     */
    private function ensureExpectedKeys(array $result): array
    {
        $expectedKeys = [
            'tags',
        ];

        foreach ($expectedKeys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = '';
            }
        }

        return $result;
    }
}
