<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Model\Table\AipromptsTable;
use App\Service\Api\Anthropic\SeoContentGenerator;
use App\Service\Api\Anthropic\ImageAnalyzer;
use App\Service\Api\Anthropic\CommentAnalyzer;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\Http\Client\Response;

class AnthropicApiService extends AbstractApiService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    private AipromptsTable $aipromptsTable;
    private SeoContentGenerator $seoContentGenerator;
    private ImageAnalyzer $imageAnalyzer;
    private CommentAnalyzer $commentAnalyzer;

    public function __construct()
    {
        $apiKey = SettingsManager::read('AI.anthropicApiKey');
        parent::__construct(new Client(), $apiKey, self::API_URL, self::API_VERSION);

        $this->aipromptsTable = TableRegistry::getTableLocator()->get('Aiprompts');
        $this->seoContentGenerator = new SeoContentGenerator($this, $this->aipromptsTable);
        $this->imageAnalyzer = new ImageAnalyzer($this, $this->aipromptsTable);
        $this->commentAnalyzer = new CommentAnalyzer($this, $this->aipromptsTable);
    }

    public function generateTagSeo(string $tagTitle, string $tagDescription): array
    {
        return $this->seoContentGenerator->generateTagSeo($tagTitle, $tagDescription);
    }

    public function generateArticleSeo(string $title, string $body): array
    {
        return $this->seoContentGenerator->generateArticleSeo($title, $body);
    }

    public function analyzeImage(string $imagePath): array
    {
        return $this->imageAnalyzer->analyze($imagePath);
    }

    public function analyzeComment(string $comment): array
    {
        return $this->commentAnalyzer->analyze($comment);
    }

    public function parseResponse(Response $response): array
    {
        $responseData = $response->getJson();
        return json_decode($responseData['content'][0]['text'], true);
    }
}