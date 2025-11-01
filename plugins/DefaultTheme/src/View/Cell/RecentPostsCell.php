<?php
declare(strict_types=1);

namespace DefaultTheme\View\Cell;

use Cake\View\Cell;

/**
 * RecentPosts cell
 *
 * Displays recent articles in sidebar or other locations
 */
class RecentPostsCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string>
     */
    protected array $_validCellOptions = ['cacheKey', 'title', 'excludeIds'];

    /**
     * Default display method
     *
     * Fetches and displays recent articles
     *
     * @param string|null $cacheKey Optional cache key for performance
     * @param string|null $title Optional custom title for the section
     * @param array $excludeIds Array of article IDs to exclude
     * @return void
     */
    public function display(?string $cacheKey = null, ?string $title = null, array $excludeIds = []): void
    {
        $conditions = [];
        if (!empty($excludeIds)) {
            $conditions['Articles.id NOT IN'] = $excludeIds;
        }

        $articlesTable = $this->fetchTable('Articles');
        $articles = $articlesTable->getRecentArticles($cacheKey, $conditions);

        $this->set('articles', $articles);
        $this->set('title', $title ?? __('Recent posts'));
    }
}
