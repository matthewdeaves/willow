<?php
declare(strict_types=1);

namespace DefaultTheme\View\Helper;

use Cake\View\Helper;

/**
 * NavigationHelper
 *
 * Handles navigation menu rendering with active state detection
 * and proper ARIA attributes for accessibility.
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class NavigationHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    protected array $helpers = ['Html', 'Url'];

    /**
     * Render main navigation menu
     *
     * @param array $menuPages Array of menu page items
     * @param int $marginBottom Bottom margin amount (Bootstrap mb-* class)
     * @return string HTML navigation menu
     */
    public function renderMainMenu(array $menuPages, int $marginBottom = 0): string
    {
        $currentUrl = $this->getView()->getRequest()->getPath();
        $output = [];

        $output[] = sprintf('<div class="nav-scroller py-1 mb-%d border-bottom">', $marginBottom);
        $output[] = '    <nav class="nav nav-underline justify-content-center" role="navigation" aria-label="' . __('Main navigation') . '">';
        $output[] = '';

        // Blog/Home link
        $output[] = $this->renderNavLink(
            __('Blog'),
            ['_name' => 'home'],
            $currentUrl
        );

        // Menu pages
        foreach ($menuPages as $menuPage) {
            $output[] = $this->renderNavLink(
                htmlspecialchars_decode($menuPage['title']),
                ['_name' => 'page-by-slug', 'slug' => $menuPage['slug']],
                $currentUrl,
                ['escape' => false]
            );
        }

        // GitHub link (external)
        $output[] = '        <a class="nav-item nav-link link-body-emphasis fw-medium px-3" ';
        $output[] = '           href="https://www.github.com/matthewdeaves/willow">';
        $output[] = '           GitHub';
        $output[] = '        </a>';

        $output[] = '    </nav>';
        $output[] = '</div>';
        $output[] = '';

        return implode("\n", $output);
    }

    /**
     * Render a single navigation link with active state detection
     *
     * @param string $title Link text
     * @param array|string $url URL array or string
     * @param string $currentUrl Current page URL for active state detection
     * @param array $options Additional link options
     * @return string HTML link element
     */
    public function renderNavLink(string $title, array|string $url, string $currentUrl, array $options = []): string
    {
        $generatedUrl = $this->Url->build($url);
        $isActive = ($currentUrl === $generatedUrl);

        $defaultOptions = [
            'class' => 'nav-item nav-link link-body-emphasis fw-medium px-3' . ($isActive ? ' active' : ''),
            'aria-current' => $isActive ? 'page' : false,
        ];

        $mergedOptions = array_merge($defaultOptions, $options);

        return '        ' . $this->Html->link($title, $url, $mergedOptions);
    }

    /**
     * Check if a URL is the current page
     *
     * @param array|string $url URL to check
     * @return bool True if URL matches current page
     */
    public function isActive(array|string $url): bool
    {
        $currentUrl = $this->getView()->getRequest()->getPath();
        $generatedUrl = $this->Url->build($url);

        return $currentUrl === $generatedUrl;
    }

    /**
     * Get CSS class for active state
     *
     * @param array|string $url URL to check
     * @param string $activeClass Class to return if active
     * @param string $inactiveClass Class to return if inactive
     * @return string CSS class
     */
    public function activeClass(array|string $url, string $activeClass = 'active', string $inactiveClass = ''): string
    {
        return $this->isActive($url) ? $activeClass : $inactiveClass;
    }

    /**
     * Render breadcrumb navigation
     *
     * @param array $crumbs Array of breadcrumb items
     * @return string HTML breadcrumb navigation
     */
    public function renderBreadcrumbs(array $crumbs): string
    {
        if (empty($crumbs)) {
            return '';
        }

        $output = [];
        $output[] = '<nav aria-label="' . __('Breadcrumb') . '">';
        $output[] = '    <ol class="breadcrumb">';

        $crumbsArray = $crumbs->toArray();
        $lastIndex = count($crumbsArray) - 1;

        foreach ($crumbsArray as $index => $crumb) {
            $isLast = ($index === $lastIndex);

            if ($isLast) {
                $output[] = sprintf(
                    '        <li class="breadcrumb-item active" aria-current="page">%s</li>',
                    h($crumb->title)
                );
            } else {
                $output[] = '        <li class="breadcrumb-item">';
                $output[] = sprintf(
                    '            %s',
                    $this->Html->link(
                        h($crumb->title),
                        ['_name' => 'page-by-slug', 'slug' => $crumb->slug]
                    )
                );
                $output[] = '        </li>';
            }
        }

        $output[] = '    </ol>';
        $output[] = '</nav>';

        return implode("\n", $output);
    }
}
