<?php
declare(strict_types=1);

namespace DefaultTheme\View\Cell;

use Cake\View\Cell;

/**
 * FeaturedPosts cell
 *
 * Displays featured articles in sidebar or other locations
 */
class FeaturedPostsCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string>
     */
    protected array $_validCellOptions = ['cacheKey', 'title'];

    /**
     * Default display method
     *
     * Fetches and displays featured articles
     *
     * @param string|null $cacheKey Optional cache key for performance
     * @param string|null $title Optional custom title for the section
     * @return void
     */
    public function display(?string $cacheKey = null, ?string $title = null): void
    {
        $articlesTable = $this->fetchTable('Articles');
        $articles = $articlesTable->getFeatured($cacheKey);

        $this->set('articles', $articles);
        $this->set('title', $title ?? __('Featured posts'));
    }
}
