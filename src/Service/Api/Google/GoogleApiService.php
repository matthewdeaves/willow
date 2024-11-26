<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use App\Utility\SettingsManager;
use Google\Cloud\Translate\V2\TranslateClient;

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
    private array $codeBlocks = [];

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
    }

    /**
     * Translates an article title and body into multiple languages using the Google Translate API.
     *
     * @param string $title The title of the article to be translated.
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
        string $body,
        string $summary,
        string $meta_title,
        string $meta_description,
        string $meta_keywords,
        string $facebook_description,
        string $linkedin_description,
        string $instagram_description,
        string $twitter_description
    ): array {
        $locales = array_filter(SettingsManager::read('Translations', []));

        $this->codeBlocks = [];

        $processedBody = $this->preprocessCodeBlocks($body);

        $translations = [];
        foreach ($locales as $locale => $enabled) {
            $translationResult = $this->translateClient->translateBatch(
                [
                    $title,
                    $processedBody,
                    $summary,
                    $meta_title,
                    $meta_description,
                    $meta_keywords,
                    $facebook_description,
                    $linkedin_description,
                    $instagram_description,
                    $twitter_description,
                ],
                [
                    'source' => 'en',
                    'target' => $locale,
                    'format' => 'html',
                ]
            );
            $translations[$locale]['title'] = $translationResult[0]['text'];
            $translations[$locale]['body'] = $this->postprocessCodeBlocks($translationResult[1]['text']);
            $translations[$locale]['summary'] = $translationResult[2]['text'];
            $translations[$locale]['meta_title'] = $translationResult[3]['text'];
            $translations[$locale]['meta_description'] = $translationResult[4]['text'];
            $translations[$locale]['meta_keywords'] = $translationResult[5]['text'];
            $translations[$locale]['facebook_description'] = $translationResult[6]['text'];
            $translations[$locale]['linkedin_description'] = $translationResult[7]['text'];
            $translations[$locale]['instagram_description'] = $translationResult[8]['text'];
            $translations[$locale]['twitter_description'] = $translationResult[9]['text'];
        }

        return $translations;
    }

    /**
     * Translates a tag title and description into multiple languages using the Google Translate API.
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
        string $twitter_description
    ): array {
        $locales = array_filter(SettingsManager::read('Translations', []));

        $translations = [];
        foreach ($locales as $locale => $enabled) {
            $translationResult = $this->translateClient->translateBatch(
                [
                    $title,
                    $description,
                    $meta_title,
                    $meta_description,
                    $meta_keywords,
                    $facebook_description,
                    $linkedin_description,
                    $instagram_description,
                    $twitter_description,
                ],
                [
                    'source' => 'en',
                    'target' => $locale,
                ]
            );
            $translations[$locale]['title'] = $translationResult[0]['text'];
            $translations[$locale]['description'] = $translationResult[1]['text'];
            $translations[$locale]['meta_title'] = $translationResult[2]['text'];
            $translations[$locale]['meta_description'] = $translationResult[3]['text'];
            $translations[$locale]['meta_keywords'] = $translationResult[4]['text'];
            $translations[$locale]['facebook_description'] = $translationResult[5]['text'];
            $translations[$locale]['linkedin_description'] = $translationResult[6]['text'];
            $translations[$locale]['instagram_description'] = $translationResult[7]['text'];
            $translations[$locale]['twitter_description'] = $translationResult[8]['text'];
        }

        return $translations;
    }

    /**
     * Preprocesses content to identify and store code blocks before translation.
     *
     * This method extracts code blocks (markdown, pre, and code tags) from the content
     * and replaces them with unique placeholders. The original code blocks are stored
     * in the $codeBlocks property for later restoration.
     *
     * @param string $content The content containing code blocks to be processed
     * @return string The content with code blocks replaced by placeholders
     */
    private function preprocessCodeBlocks(string $content): string
    {
        // First, let's identify and store code blocks
        $codeBlocks = [];
        $content = preg_replace_callback(
            '/(```[a-z]*\n[\s\S]*?\n```)|(<pre[\s\S]*?<\/pre>)|(<code[\s\S]*?<\/code>)/m',
            function ($matches) use (&$codeBlocks) {
                $placeholder = sprintf('__CODE_BLOCK_%d__', count($codeBlocks));
                $codeBlocks[$placeholder] = $matches[0];

                return $placeholder;
            },
            $content
        );

        // Store the code blocks in a property for later use
        $this->codeBlocks = $codeBlocks;

        return $content;
    }

    /**
     * Restores previously stored code blocks back into the content.
     *
     * This method replaces the placeholder tokens with their original code block content
     * that was stored during preprocessing. This ensures code blocks maintain their
     * original formatting and content after translation.
     *
     * @param string $content The content containing code block placeholders
     * @return string The content with original code blocks restored
     */
    private function postprocessCodeBlocks(string $content): string
    {
        // Restore the original code blocks
        foreach ($this->codeBlocks as $placeholder => $codeBlock) {
            $content = str_replace($placeholder, $codeBlock, $content);
        }

        return $content;
    }
}
