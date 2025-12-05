<?php
declare(strict_types=1);

namespace DefaultTheme\View\Helper;

use App\Utility\SettingsManager;
use Cake\View\Helper;

/**
 * MetaTagsHelper
 *
 * Generates SEO-optimized meta tags for HTML head sections.
 * Handles standard meta tags, Open Graph, Twitter Cards, LinkedIn, and Instagram.
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class MetaTagsHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    protected array $helpers = ['Html', 'Url'];

    /**
     * Render complete meta tags for a given model (article, tag, etc.)
     *
     * @param object|null $model The model entity to generate meta tags for
     * @return string HTML meta tags
     */
    public function render(?object $model = null): string
    {
        if ($model) {
            return $this->renderModelMetaTags($model);
        }

        return $this->renderDefaultMetaTags();
    }

    /**
     * Render meta tags for a specific model entity
     *
     * @param object $model The model entity
     * @return string HTML meta tags
     */
    protected function renderModelMetaTags(object $model): string
    {
        $tags = [];

        // Basic meta tags
        $tags[] = $this->Html->meta('title', $model->meta_title ?: $model->title);
        $tags[] = $this->Html->meta('description', $this->getMetaDescription($model));
        $tags[] = $this->Html->meta('keywords', $model->meta_keywords ?? '');

        // Open Graph / Facebook
        $tags[] = $this->renderOpenGraphTags($model);

        // Twitter
        $tags[] = $this->renderTwitterTags($model);

        // LinkedIn
        $tags[] = $this->renderLinkedInTags($model);

        // Instagram
        $tags[] = $this->renderInstagramTags($model);

        return implode("\n    ", array_filter($tags));
    }

    /**
     * Render default site meta tags when no model is provided
     *
     * @return string HTML meta tags
     */
    protected function renderDefaultMetaTags(): string
    {
        $tags = [];

        $tags[] = $this->Html->meta(
            'title',
            SettingsManager::read('SEO.siteStrapline', '')
        );

        $tags[] = $this->Html->meta(
            'description',
            SettingsManager::read('SEO.siteMetaDescription', 'Meta Description')
        );

        $tags[] = $this->Html->meta(
            'keywords',
            SettingsManager::read('SEO.siteMetakeywords', 'Meta Keywords')
        );

        return implode("\n    ", array_filter($tags));
    }

    /**
     * Get meta description from model with fallback
     *
     * @param object $model The model entity
     * @return string Meta description
     */
    protected function getMetaDescription(object $model): string
    {
        if (!empty($model->meta_description)) {
            return $model->meta_description;
        }

        $content = $model->description ?? $model->body ?? '';

        return substr(strip_tags($content), 0, 160);
    }

    /**
     * Render Open Graph meta tags
     *
     * @param object $model The model entity
     * @return string HTML meta tags
     */
    protected function renderOpenGraphTags(object $model): string
    {
        $tags = [];

        $tags[] = '<meta property="og:type" content="article">';
        $tags[] = sprintf(
            '<meta property="og:url" content="%s">',
            h($this->Url->build('/' . $model->slug, ['fullBase' => true]))
        );
        $tags[] = sprintf(
            '<meta property="og:title" content="%s">',
            h($model->meta_title ?: $model->title)
        );
        $tags[] = sprintf(
            '<meta property="og:description" content="%s">',
            h($model->facebook_description ?: $model->meta_description)
        );

        if (!empty($model->image_url)) {
            $tags[] = sprintf(
                '<meta property="og:image" content="%s">',
                h($this->Url->build($model->image_url, ['fullBase' => true]))
            );
        }

        $publishedTime = $model->published ?? $model->modified;
        $tags[] = sprintf(
            '<meta property="article:published_time" content="%s">',
            $publishedTime->format('c')
        );
        $tags[] = sprintf(
            '<meta property="article:modified_time" content="%s">',
            $model->modified->format('c')
        );

        return implode("\n    ", $tags);
    }

    /**
     * Render Twitter Card meta tags
     *
     * @param object $model The model entity
     * @return string HTML meta tags
     */
    protected function renderTwitterTags(object $model): string
    {
        $tags = [];

        $tags[] = '<meta name="twitter:card" content="summary_large_image">';
        $tags[] = sprintf(
            '<meta name="twitter:url" content="%s">',
            h($this->Url->build('/' . $model->slug, ['fullBase' => true]))
        );
        $tags[] = sprintf(
            '<meta name="twitter:title" content="%s">',
            h($model->meta_title ?: $model->title)
        );
        $tags[] = sprintf(
            '<meta name="twitter:description" content="%s">',
            h($model->twitter_description ?: $model->meta_description)
        );

        return implode("\n    ", $tags);
    }

    /**
     * Render LinkedIn meta tags
     *
     * @param object $model The model entity
     * @return string HTML meta tags
     */
    protected function renderLinkedInTags(object $model): string
    {
        $tags = [];

        $tags[] = sprintf(
            '<meta name="linkedin:title" content="%s">',
            h($model->meta_title ?: $model->title)
        );
        $tags[] = sprintf(
            '<meta name="linkedin:description" content="%s">',
            h($model->linkedin_description ?: $model->meta_description)
        );

        return implode("\n    ", $tags);
    }

    /**
     * Render Instagram meta tags
     *
     * @param object $model The model entity
     * @return string HTML meta tags
     */
    protected function renderInstagramTags(object $model): string
    {
        $tags = [];

        $tags[] = sprintf(
            '<meta name="instagram:title" content="%s">',
            h($model->meta_title ?: $model->title)
        );
        $tags[] = sprintf(
            '<meta name="instagram:description" content="%s">',
            h($model->instagram_description ?: $model->meta_description)
        );

        return implode("\n    ", $tags);
    }

    /**
     * Render title tag with site name
     *
     * @param string|null $pageTitle The page title
     * @return string HTML title tag
     */
    public function title(?string $pageTitle = null): string
    {
        $siteName = SettingsManager::read('SEO.siteName', 'Willow CMS');

        if ($pageTitle) {
            return sprintf('<title>%s: %s</title>', h($siteName), h($pageTitle));
        }

        return sprintf('<title>%s</title>', h($siteName));
    }
}
