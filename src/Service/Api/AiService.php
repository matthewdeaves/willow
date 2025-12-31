<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Model\Table\AipromptsTable;
use App\Service\Api\Anthropic\ArticleTagsGenerator;
use App\Service\Api\Anthropic\CommentAnalyzer;
use App\Service\Api\Anthropic\ImageAnalyzer;
use App\Service\Api\Anthropic\SeoContentGenerator;
use App\Service\Api\Anthropic\TextSummaryGenerator;
use App\Service\Api\Anthropic\TranslationGenerator;
use Cake\ORM\TableRegistry;

/**
 * AiService Class
 *
 * Unified AI service that uses the configured provider (Anthropic or OpenRouter).
 * This class provides the same API as AnthropicApiService but supports provider switching
 * based on application settings.
 *
 * Jobs and other consumers should use this class to benefit from provider flexibility.
 */
class AiService
{
    /**
     * @var \App\Service\Api\AiProviderInterface The AI provider instance.
     */
    private AiProviderInterface $provider;

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

    /**
     * @var \App\Service\Api\Anthropic\ArticleTagsGenerator The article tags generator service.
     */
    private ArticleTagsGenerator $articleTagsGenerator;

    /**
     * @var \App\Service\Api\Anthropic\TextSummaryGenerator The text summary generator service.
     */
    private TextSummaryGenerator $textSummaryGenerator;

    /**
     * @var \App\Service\Api\Anthropic\TranslationGenerator The translation generator service.
     */
    private TranslationGenerator $translationGenerator;

    /**
     * AiService constructor.
     *
     * Creates the service with the configured provider or an injected one for testing.
     *
     * @param \App\Service\Api\AiProviderInterface|null $provider Optional provider for testing.
     */
    public function __construct(?AiProviderInterface $provider = null)
    {
        $this->provider = $provider ?? AiServiceFactory::createProvider();
        $this->aipromptsTable = TableRegistry::getTableLocator()->get('Aiprompts');

        $this->seoContentGenerator = new SeoContentGenerator($this->provider, $this->aipromptsTable);
        $this->imageAnalyzer = new ImageAnalyzer($this->provider, $this->aipromptsTable);
        $this->commentAnalyzer = new CommentAnalyzer($this->provider, $this->aipromptsTable);
        $this->articleTagsGenerator = new ArticleTagsGenerator($this->provider, $this->aipromptsTable);
        $this->textSummaryGenerator = new TextSummaryGenerator($this->provider, $this->aipromptsTable);
        $this->translationGenerator = new TranslationGenerator($this->provider, $this->aipromptsTable);
    }

    /**
     * Gets the current provider instance.
     *
     * @return \App\Service\Api\AiProviderInterface The AI provider.
     */
    public function getProvider(): AiProviderInterface
    {
        return $this->provider;
    }

    /**
     * Gets the provider name.
     *
     * @return string The provider identifier.
     */
    public function getProviderName(): string
    {
        return $this->provider->getProviderName();
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
     * Generates SEO content for an image gallery.
     *
     * @param string $name The name of the gallery.
     * @param string $context Additional context about the gallery content and images.
     * @return array The generated SEO content.
     */
    public function generateGallerySeo(string $name, string $context): array
    {
        return $this->seoContentGenerator->generateGallerySeo($name, $context);
    }

    /**
     * Generates tags for an article.
     *
     * @param array $allTags All available tags.
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated article tags.
     */
    public function generateArticleTags(array $allTags, string $title, string $body): array
    {
        return $this->articleTagsGenerator->generateArticleTags($allTags, $title, $body);
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
     * Generates a summary for a given text.
     *
     * @param string $context The context of the text (e.g., 'article', 'page', 'report').
     * @param string $text The text to summarize.
     * @return array The generated summary.
     */
    public function generateTextSummary(string $context, string $text): array
    {
        return $this->textSummaryGenerator->generateTextSummary($context, $text);
    }

    /**
     * Translates an array of strings from one locale to another.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The locale code of the source language (e.g., 'en_US').
     * @param string $localeTo The locale code of the target language (e.g., 'fr_FR').
     * @return array The translated strings.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        return $this->translationGenerator->generateTranslation($strings, $localeFrom, $localeTo);
    }
}
