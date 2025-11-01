<?php
declare(strict_types=1);

namespace DefaultTheme\View\Helper;

use App\Utility\SettingsManager;
use Cake\View\Helper;

/**
 * SiteConfigHelper
 *
 * Provides a clean interface for accessing site configuration in templates.
 * Acts as a decoupling layer between templates and SettingsManager utility.
 *
 * This helper makes templates more testable and reduces direct dependencies
 * on infrastructure utilities.
 */
class SiteConfigHelper extends Helper
{
    /**
     * Get a site configuration value
     *
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return SettingsManager::read($key, $default);
    }

    /**
     * Get site name
     *
     * @return string Site name
     */
    public function siteName(): string
    {
        return SettingsManager::read('SEO.siteName', 'Willow CMS');
    }

    /**
     * Get site strapline/tagline
     *
     * @return string Site strapline
     */
    public function strapline(): string
    {
        return SettingsManager::read('SEO.siteStrapline', '');
    }

    /**
     * Get site meta description
     *
     * @return string Meta description
     */
    public function metaDescription(): string
    {
        return SettingsManager::read('SEO.siteMetaDescription', '');
    }

    /**
     * Get site meta keywords
     *
     * @return string Meta keywords
     */
    public function metaKeywords(): string
    {
        return SettingsManager::read('SEO.siteMetakeywords', '');
    }

    /**
     * Check if comments are enabled for articles
     *
     * @return bool True if enabled
     */
    public function areArticleCommentsEnabled(): bool
    {
        return (bool)SettingsManager::read('Comments.articlesEnabled', false);
    }

    /**
     * Check if comments are enabled for pages
     *
     * @return bool True if enabled
     */
    public function arePageCommentsEnabled(): bool
    {
        return (bool)SettingsManager::read('Comments.pagesEnabled', false);
    }

    /**
     * Check if comments are enabled for a given content type
     *
     * @param string $kind Content type ('article' or 'page')
     * @return bool True if enabled
     */
    public function areCommentsEnabledFor(string $kind): bool
    {
        return match ($kind) {
            'article' => $this->areArticleCommentsEnabled(),
            'page' => $this->arePageCommentsEnabled(),
            default => false,
        };
    }

    /**
     * Get Google Tag Manager head code
     *
     * @return string GTM head code
     */
    public function googleTagManagerHead(): string
    {
        return SettingsManager::read('Google.tagManagerHead', '');
    }

    /**
     * Get main menu display setting for pages
     *
     * @return string 'root' or 'selected'
     */
    public function mainMenuPagesDisplay(): string
    {
        return SettingsManager::read('SitePages.mainMenuShow', 'root');
    }

    /**
     * Get main menu display setting for tags
     *
     * @return string 'root' or 'selected'
     */
    public function mainMenuTagsDisplay(): string
    {
        return SettingsManager::read('SitePages.mainTagMenuShow', 'root');
    }

    /**
     * Get privacy policy article ID
     *
     * @return string|null Privacy policy article ID
     */
    public function privacyPolicyId(): ?string
    {
        $id = SettingsManager::read('SitePages.privacyPolicy', null);

        return ($id && $id !== 'None') ? $id : null;
    }
}
