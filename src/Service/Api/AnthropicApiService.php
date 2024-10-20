<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Model\Table\AipromptsTable;
use App\Service\Api\Anthropic\CommentAnalyzer;
use App\Service\Api\Anthropic\ImageAnalyzer;
use App\Service\Api\Anthropic\SeoContentGenerator;
use App\Service\Api\Anthropic\TagGenerator;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\ORM\TableRegistry;

/**
 * AnthropicApiService Class
 *
 * This service class provides an interface to interact with the Anthropic API,
 * handling various AI-related tasks such as SEO content generation, image analysis,
 * and comment moderation.
 */
class AnthropicApiService extends AbstractApiService
{
    /**
     * The base URL for the Anthropic API.
     *
     * @var string
     */
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    /**
     * The version of the Anthropic API being used.
     *
     * @var string
     */
    private const API_VERSION = '2023-06-01';

    /**
     * @var \App\Model\Table\AipromptsTable The table instance for AI prompts.
     */
    private AipromptsTable $aipromptsTable;

    /**
     * @var \App\Service\Api\Anthropic\SeoContentGenerator The SEO content generator service.
     */
    private SeoContentGenerator $seoContentGenerator;

    /**
     * @var \App\Service\Api\Anthropic\ImageAnalyzer The image analyzer service.
     */
    private ImageAnalyzer $imageAnalyzer;

    /**
     * @var \App\Service\Api\Anthropic\CommentAnalyzer The comment analyzer service.
     */
    private CommentAnalyzer $commentAnalyzer;

    private TagGenerator $tagGenerator;

    /**
     * AnthropicApiService constructor.
     *
     * Initializes the service with necessary dependencies and configurations.
     */
    public function __construct()
    {
        $apiKey = SettingsManager::read('AI.anthropicApiKey');
        parent::__construct(new Client(), $apiKey, self::API_URL, self::API_VERSION);

        $this->aipromptsTable = TableRegistry::getTableLocator()->get('Aiprompts');
        $this->seoContentGenerator = new SeoContentGenerator($this, $this->aipromptsTable);
        $this->imageAnalyzer = new ImageAnalyzer($this, $this->aipromptsTable);
        $this->commentAnalyzer = new CommentAnalyzer($this, $this->aipromptsTable);
        $this->tagGenerator = new TagGenerator($this, $this->aipromptsTable);
    }

    /**
     * Generates SEO content for a tag.
     *
     * @param string $tagTitle The title of the tag.
     * @param string $tagDescription The description of the tag.
     * @return array The generated SEO content.
     */
    public function generateTagSeo(string $tagTitle, string $tagDescription): array
    {
        return $this->seoContentGenerator->generateTagSeo($tagTitle, $tagDescription);
    }

    /**
     * Generates SEO content for an article.
     *
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated SEO content.
     */
    public function generateArticleSeo(string $title, string $body): array
    {
        return $this->seoContentGenerator->generateArticleSeo($title, $body);
    }

    /**
     * Generates tags for an article.
     *
     * @param array $existingTags The list of existing tags in the CMS.
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated tags.
     */
    public function generateArticleTags(array $existingTags, string $title, string $body): array
    {
        return $this->tagGenerator->generateTags($existingTags, $title, $body);
    }

    /**
     * Analyzes an image.
     *
     * @param string $imagePath The path to the image file.
     * @return array The analysis results.
     */
    public function analyzeImage(string $imagePath): array
    {
        return $this->imageAnalyzer->analyze($imagePath);
    }

    /**
     * Analyzes a comment.
     *
     * @param string $comment The comment text to analyze.
     * @return array The analysis results.
     */
    public function analyzeComment(string $comment): array
    {
        return $this->commentAnalyzer->analyze($comment);
    }

    /**
     * Parses the response from the API.
     *
     * @param \Cake\Http\Client\Response $response The HTTP response from the API.
     * @return array The parsed response data.
     */
    public function parseResponse(Response $response): array
    {
        $responseData = $response->getJson();

        return json_decode($responseData['content'][0]['text'], true);
    }
}
