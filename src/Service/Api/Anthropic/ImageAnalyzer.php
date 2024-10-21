<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\AnthropicApiService;
use InvalidArgumentException;

/**
 * ImageAnalyzer Class
 *
 * This class is responsible for analyzing images using the Anthropic API service.
 * It interacts with the AI prompts table to retrieve prompt data and uses the AnthropicApiService
 * to send requests and parse responses for image analysis.
 */
class ImageAnalyzer
{
    /**
     * The Anthropic API service used for sending requests and parsing responses.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $apiService;

    /**
     * The AI prompts table for retrieving prompt data necessary for image analysis.
     *
     * @var \App\Model\Table\AipromptsTable
     */
    private AipromptsTable $aipromptsTable;

    /**
     * ImageAnalyzer constructor.
     *
     * Initializes the API service and AI prompts table for image analysis.
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
     * Analyzes an image using the Anthropic API.
     *
     * This method performs the following steps:
     * 1. Validates the existence of the image file.
     * 2. Encodes the image file to base64 and determines its MIME type.
     * 3. Retrieves the appropriate prompt data for image analysis.
     * 4. Creates a payload with the image data and prompt information.
     * 5. Sends a request to the Anthropic API and processes the response.
     *
     * @param string $imagePath The path to the image file to be analyzed.
     * @return array The analysis results from the API, containing various aspects of the image analysis.
     * @throws \InvalidArgumentException If the image file is not found or the task prompt data is not found.
     */
    public function analyze(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image file not found: {$imagePath}");
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);

        $promptData = $this->getPromptData('image_analysis');
        $payload = $this->createPayload($promptData, $imageData, $mimeType);

        $response = $this->apiService->sendRequest($payload);

        return $this->apiService->parseResponse($response);
    }

    /**
     * Creates a payload for the API request using the provided prompt data and image information.
     *
     * @param array $promptData The prompt data retrieved from the AI prompts table.
     * @param string $imageData The base64 encoded image data.
     * @param string $mimeType The MIME type of the image.
     * @return array The created payload for the API request.
     */
    private function createPayload(array $promptData, string $imageData, string $mimeType): array
    {
        return [
            'model' => $promptData['model'],
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => $promptData['temperature'],
            'system' => $promptData['system_prompt'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mimeType,
                                'data' => $imageData,
                            ],
                        ],
                    ],
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
