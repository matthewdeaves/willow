<?php
declare(strict_types=1);

namespace DefaultTheme\View\Cell;

use Cake\View\Cell;

/**
 * Archives cell
 *
 * Displays article archives organized by year and month
 */
class ArchivesCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string>
     */
    protected array $_validCellOptions = ['cacheKey'];

    /**
     * Default display method
     *
     * Fetches and displays article archive dates
     *
     * @param string|null $cacheKey Optional cache key for performance
     * @return void
     */
    public function display(?string $cacheKey = null): void
    {
        $articlesTable = $this->fetchTable('Articles');

        // Generate cache key if not provided
        if ($cacheKey === null) {
            $lang = $this->request->getParam('lang', 'en');
            $cacheKey = 'archives_' . $lang . '_';
        }

        $articleArchives = $articlesTable->getArchiveDates($cacheKey);

        $this->set('articleArchives', $articleArchives);
    }
}
