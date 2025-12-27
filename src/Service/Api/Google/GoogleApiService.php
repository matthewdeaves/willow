<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use App\Utility\SettingsManager;
use Exception;
use Google\Cloud\Translate\V2\TranslateClient;
use InvalidArgumentException;

/**
 * Service class for interacting with the Google Cloud Translate API.
 */
class GoogleApiService
{
    /**
     * The Google Cloud Translate client instance.
     *
     * @var \Google\Cloud\Translate\V2\TranslateClient
     */
    private TranslateClient $translateClient;

    /**
     * @var array<string, string>
     */
    private array $preservedBlocks = [];

    /**
     * Constructor for the GoogleApiService class.
     * Initializes the Google Cloud Translate client with the API key from the settings.
     */
    public function __construct()
    {
        $this->translateClient = new TranslateClient([
            'key' => SettingsManager::read('Google.translateApiKey', env('TRANSLATE_API_KEY')),
        ]);
    }

    /**
     * Translates an array of strings from one language to another using batch translation.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The source language locale code (e.g., 'en' for English).
     * @param string $localeTo The target language locale code (e.g., 'fr' for French).
     * @return array An array containing the translated strings and their original values.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        if (empty($strings)) {
            return ['translations' => []];
        }

        // Google Translate API has batch size limits
        if (count($strings) > 100) {
            throw new InvalidArgumentException('Batch size exceeds Google API limit');
        }

        try {
            $results = $this->translateClient->translateBatch($strings, [
                'source' => $localeFrom,
                'target' => $localeTo,
            ]);

            $translatedStrings = [];

            foreach ($results as $result) {
                $translatedStrings['translations'][] = [
                    'original' => $result['input'],
                    'translated' => $result['text'],
                ];
            }

            return $translatedStrings;
        } catch (Exception $e) {
            throw new TranslationException('Translation failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Translates content fields into multiple languages using the Google Translate API.
     *
     * This is the core translation method that handles the common logic for all content types.
     * It supports HTML content preprocessing for fields that may contain code blocks, videos,
     * or galleries.
     *
     * @param array<string, string> $fields Associative array of field name => value to translate
     * @param array<string> $htmlFields Field names that should be preprocessed for HTML content
     * @param bool $useHtmlFormat Whether to use HTML format for translation (preserves HTML tags)
     * @return array<string, array<string, string>> Translations keyed by locale, then field name
     */
    public function translateContent(array $fields, array $htmlFields = [], bool $useHtmlFormat = false): array
    {
        $locales = array_filter(SettingsManager::read('Translations', []));

        if (empty($locales)) {
            return [];
        }

        $this->preservedBlocks = [];

        // Preprocess HTML fields
        $processedFields = [];
        foreach ($fields as $fieldName => $value) {
            if (in_array($fieldName, $htmlFields, true)) {
                $processedFields[$fieldName] = $this->preprocessContent($value);
            } else {
                $processedFields[$fieldName] = $value;
            }
        }

        $fieldNames = array_keys($processedFields);
        $fieldValues = array_values($processedFields);

        $translations = [];
        foreach ($locales as $locale => $enabled) {
            $options = [
                'source' => 'en',
                'target' => $locale,
            ];

            if ($useHtmlFormat) {
                $options['format'] = 'html';
            }

            $translationResult = $this->translateClient->translateBatch($fieldValues, $options);

            foreach ($fieldNames as $index => $fieldName) {
                $translatedText = $translationResult[$index]['text'];

                // Postprocess HTML fields to restore preserved blocks
                if (in_array($fieldName, $htmlFields, true)) {
                    $translatedText = $this->postprocessContent($translatedText);
                }

                $translations[$locale][$fieldName] = $translatedText;
            }
        }

        return $translations;
    }

    /**
     * Translates an article into multiple languages using the Google Translate API.
     *
     * @param string $title The title of the article to be translated.
     * @param string $lede The lede of the article to be translated.
     * @param string $body The body of the article to be translated (can contain HTML).
     * @param string $summary The summary of the article to be translated.
     * @param string $meta_title The meta title of the article to be translated.
     * @param string $meta_description The meta description of the article to be translated.
     * @param string $meta_keywords The meta keywords of the article to be translated.
     * @param string $facebook_description The Facebook description of the article to be translated.
     * @param string $linkedin_description The LinkedIn description of the article to be translated.
     * @param string $instagram_description The Instagram description of the article to be translated.
     * @param string $twitter_description The Twitter description of the article to be translated.
     * @return array An associative array where the keys are the locale codes and the values are arrays
     *               containing the translated fields for each enabled locale.
     */
    public function translateArticle(
        string $title,
        string $lede,
        string $body,
        string $summary,
        string $meta_title,
        string $meta_description,
        string $meta_keywords,
        string $facebook_description,
        string $linkedin_description,
        string $instagram_description,
        string $twitter_description,
    ): array {
        return $this->translateContent(
            [
                'title' => $title,
                'lede' => $lede,
                'body' => $body,
                'summary' => $summary,
                'meta_title' => $meta_title,
                'meta_description' => $meta_description,
                'meta_keywords' => $meta_keywords,
                'facebook_description' => $facebook_description,
                'linkedin_description' => $linkedin_description,
                'instagram_description' => $instagram_description,
                'twitter_description' => $twitter_description,
            ],
            ['body'], // HTML fields that need preprocessing
            true, // Use HTML format
        );
    }

    /**
     * Translates a tag into multiple languages using the Google Translate API.
     *
     * @param string $title The title of the tag to be translated.
     * @param string $description The description of the tag to be translated.
     * @param string $meta_title The meta title of the tag to be translated.
     * @param string $meta_description The meta description of the tag to be translated.
     * @param string $meta_keywords The meta keywords of the tag to be translated.
     * @param string $facebook_description The Facebook description of the tag to be translated.
     * @param string $linkedin_description The LinkedIn description of the tag to be translated.
     * @param string $instagram_description The Instagram description of the tag to be translated.
     * @param string $twitter_description The Twitter description of the tag to be translated.
     * @return array An associative array where the keys are the locale codes and the values are arrays
     *               containing the translated fields for each enabled locale.
     */
    public function translateTag(
        string $title,
        string $description,
        string $meta_title,
        string $meta_description,
        string $meta_keywords,
        string $facebook_description,
        string $linkedin_description,
        string $instagram_description,
        string $twitter_description,
    ): array {
        return $this->translateContent([
            'title' => $title,
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'facebook_description' => $facebook_description,
            'linkedin_description' => $linkedin_description,
            'instagram_description' => $instagram_description,
            'twitter_description' => $twitter_description,
        ]);
    }

    /**
     * Translates an image gallery into multiple languages using the Google Translate API.
     *
     * @param string $name The name of the gallery to be translated.
     * @param string $description The description of the gallery to be translated.
     * @param string $meta_title The meta title of the gallery to be translated.
     * @param string $meta_description The meta description of the gallery to be translated.
     * @param string $meta_keywords The meta keywords of the gallery to be translated.
     * @param string $facebook_description The Facebook description of the gallery to be translated.
     * @param string $linkedin_description The LinkedIn description of the gallery to be translated.
     * @param string $instagram_description The Instagram description of the gallery to be translated.
     * @param string $twitter_description The Twitter description of the gallery to be translated.
     * @return array An associative array where the keys are the locale codes and the values are arrays
     *               containing the translated fields for each enabled locale.
     */
    public function translateImageGallery(
        string $name,
        string $description,
        string $meta_title,
        string $meta_description,
        string $meta_keywords,
        string $facebook_description,
        string $linkedin_description,
        string $instagram_description,
        string $twitter_description,
    ): array {
        return $this->translateContent([
            'name' => $name,
            'description' => $description,
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'facebook_description' => $facebook_description,
            'linkedin_description' => $linkedin_description,
            'instagram_description' => $instagram_description,
            'twitter_description' => $twitter_description,
        ]);
    }

    /**
     * Preprocesses content to identify and store code blocks, video placeholders, and gallery placeholders before translation.
     *
     * This method extracts code blocks (markdown, pre, code tags), video placeholders, and image gallery
     * placeholders from the content and replaces them with unique placeholders. The original content is
     * stored in the $preservedBlocks property for later restoration.
     *
     * @param string $content The content containing blocks to be processed
     * @return string The content with preserved blocks replaced by placeholders
     */
    private function preprocessContent(string $content): string
    {
        $this->preservedBlocks = [];

        // Process code blocks, video placeholders, and gallery placeholders
        $patterns = [
            // Code blocks pattern
            '/(```[a-z]*\n[\s\S]*?\n```)|(<pre[\s\S]*?<\/pre>)|(<code[\s\S]*?<\/code>)/m',
            // YouTube video placeholder pattern
            '/\[youtube:[a-zA-Z0-9_-]+:\d+:\d+:[^\]]*\]/m',
            // Image gallery placeholder pattern
            '/\[gallery:[a-f0-9-]+:[^:]*:[^\]]*\]/m',
        ];

        foreach ($patterns as $pattern) {
            $content = preg_replace_callback(
                $pattern,
                function ($matches) {
                    // Use HTML comment syntax for placeholders to prevent translation
                    $placeholder = sprintf('<!--PRESERVED_BLOCK_%d-->', count($this->preservedBlocks));
                    $this->preservedBlocks[$placeholder] = $matches[0];

                    return $placeholder;
                },
                $content,
            );
        }

        return $content;
    }

    /**
     * Restores previously stored content blocks back into the content.
     *
     * This method replaces the placeholder tokens with their original content
     * that was stored during preprocessing. This ensures code blocks and video
     * placeholders maintain their original formatting and content after translation.
     *
     * @param string $content The content containing placeholders
     * @return string The content with original blocks restored
     */
    private function postprocessContent(string $content): string
    {
        // Restore all preserved blocks
        foreach ($this->preservedBlocks as $placeholder => $originalContent) {
            $content = str_replace($placeholder, $originalContent, $content);
        }

        return $content;
    }
}
