<?php
declare(strict_types=1);

namespace DefaultTheme\View\Cell;

use Cake\View\Cell;

/**
 * RelatedPages cell
 *
 * Displays related or child pages for a given article
 */
class RelatedPagesCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string>
     */
    protected array $_validCellOptions = ['articleId', 'cacheKey', 'title'];

    /**
     * Default display method
     *
     * Fetches and displays child pages for an article
     *
     * @param string|null $articleId The article ID to get children for
     * @param string|null $cacheKey Optional cache key for performance
     * @param string|null $title Optional custom title for the section
     * @return void
     */
    public function display(?string $articleId = null, ?string $cacheKey = null, ?string $title = null): void
    {
        $pages = [];

        if ($articleId) {
            $articlesTable = $this->fetchTable('Articles');
            $pages = $articlesTable->find('children', for: $articleId)
                ->select(['id', 'title', 'slug', 'lede', 'published', 'image_id'])
                ->contain(['Images'])
                ->where(['Articles.is_published' => true])
                ->cache($cacheKey . '_children', 'content')
                ->all();
        }

        $this->set('pages', $pages);
        $this->set('title', $title ?? __('Related pages'));
    }
}
