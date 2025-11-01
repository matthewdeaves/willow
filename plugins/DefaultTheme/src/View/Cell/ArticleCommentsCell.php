<?php
declare(strict_types=1);

namespace DefaultTheme\View\Cell;

use Cake\View\Cell;

/**
 * ArticleComments cell
 *
 * Handles displaying comments and comment form for articles
 * Encapsulates all comment-related presentation logic
 */
class ArticleCommentsCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string>
     */
    protected array $_validCellOptions = ['article'];

    /**
     * Default display method
     *
     * Sets up comment display and form for an article
     *
     * @param object $article The article entity with comments
     * @return void
     */
    public function display(object $article): void
    {
        $this->set('article', $article);
        $this->set('comments', $article->comments ?? []);
    }
}
