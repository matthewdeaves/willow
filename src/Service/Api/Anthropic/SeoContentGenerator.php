<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\AnthropicApiService;
use InvalidArgumentException;

class SeoContentGenerator
{
    private AnthropicApiService $apiService;
    private AipromptsTable $aipromptsTable;

    public function __construct(AnthropicApiService $apiService, AipromptsTable $aipromptsTable)
    {
        $this->apiService = $apiService;
        $this->aipromptsTable = $aipromptsTable;
    }

    public function generateTagSeo(string $tagTitle, string $tagDescription): array
    {
        $promptData = $this->getPromptData('tag_seo_analysis');
        $payload = $this->createPayload($promptData, [
            'Tag Title' => $tagTitle,
            'Tag Description' => $tagDescription,
        ]);

        $response = $this->apiService->sendRequest($payload);
        $result = $this->apiService->parseResponse($response);

        return $this->ensureExpectedKeys($result);
    }

    public function generateArticleSeo(string $title, string $body): array
    {
        $plainTextContent = strip_tags(html_entity_decode($body));
        $promptData = $this->getPromptData('article_seo_analysis');
        $payload = $this->createPayload($promptData, [
            'Title' => $title,
            'Content' => $plainTextContent,
        ]);

        $response = $this->apiService->sendRequest($payload);
        $result = $this->apiService->parseResponse($response);

        return $this->ensureExpectedKeys($result);
    }

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
        ];

        foreach ($expectedKeys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = '';
            }
        }

        return $result;
    }
}
