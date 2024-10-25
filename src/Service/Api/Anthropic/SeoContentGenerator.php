<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use InvalidArgumentException;

/**
 * SeoContentGenerator Class
 *
 * This class is responsible for generating SEO content for tags and articles
 * using the Anthropic API service. It interacts with the AI prompts table to retrieve
 * prompt data and uses the AnthropicApiService to send requests and parse responses.
 */
class SeoContentGenerator
{
    /**
     * The Anthropic API service used for sending requests and parsing responses.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $apiService;

    /**
     * The AI prompts table for retrieving prompt data necessary for SEO content generation.
     *
     * @var \App\Model\Table\AipromptsTable
     */
    private AipromptsTable $aipromptsTable;

    /**
     * SeoContentGenerator constructor.
     *
     * Initializes the API service and AI prompts table for SEO content generation.
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
     * This method performs the following steps:
     * 1. Retrieves the appropriate prompt data for tag SEO analysis.
     * 2. Creates a payload with the tag title and description.
     * 3. Sends a request to the Anthropic API and processes the response.
     * 4. Ensures all expected SEO keys are present in the result.
     *
     * @param string $tagTitle The title of the tag.
     * @param string $tagDescription The description of the tag.
     * @return array The generated SEO content, including meta tags and social media descriptions.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function generateTagSeo(string $tagTitle, string $tagDescription): array
    {
        $promptData = $this->getPromptData('tag_seo_analysis');
        $payload = $this->createPayload($promptData, [
            'tag_title' => $tagTitle,
            'tag_description' => $tagDescription,
        ]);

        $response = $this->apiService->sendRequest($payload);
        $result = $this->apiService->parseResponse($response);

        return $this->ensureExpectedKeys($result);
    }

    /**
     * Generates SEO content for an article.
     *
     * This method performs the following steps:
     * 1. Strips HTML tags and decodes entities from the article body.
     * 2. Retrieves the appropriate prompt data for article SEO analysis.
     * 3. Creates a payload with the article title and plain text content.
     * 4. Sends a request to the Anthropic API and processes the response.
     * 5. Ensures all expected SEO keys are present in the result.
     *
     * @param string $title The title of the article.
     * @param string $body The body content of the article (may contain HTML).
     * @return array The generated SEO content, including meta tags and social media descriptions.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function generateArticleSeo(string $title, string $body): array
    {
        $plainTextContent = strip_tags(html_entity_decode($body));
        $promptData = $this->getPromptData('article_seo_analysis');
        $payload = $this->createPayload($promptData, [
            'article_title' => $title,
            'article_content' => $plainTextContent,
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
            'meta_title',
            'meta_description',
            'meta_keywords',
            'facebook_description',
            'linkedin_description',
            'twitter_description',
            'instagram_description',
            'description',
        ];

        foreach ($expectedKeys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = '';
            }
        }

        return $result;
    }
}
