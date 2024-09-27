<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var array $articles
 */

// Recursive function to build the nested menu
function buildMenu($articles, $view) {
    echo '<ul class="nested-menu">';
    foreach ($articles as $page) {
        echo '<li>';
        echo $view->Html->link(
            h($page->title),
            ['prefix' => false, 'controller' => 'Articles', 'action' => 'viewBySlug', $page->slug],
            ['class' => 'menu-item']
        );
        if (!empty($page->children)) {
            buildMenu($page->children, $view);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>

<div class="article-container">
    <?php if ($article) : ?>
    <article>
        <header>
            <h1 class="article-title"><?= h($article->title) ?></h1>
            <time class="article-date" datetime="<?= $article->modified->format('Y-m-d H:i:s') ?>">
                Last updated: <?= $article->modified->format('F j, Y, g:i a') ?>
            </time>
        </header>

        <div class="article-content">
            <?= $article->body ?>
        </div>
    </article>
    <?php endif; ?>
    <?php if ($articles) : ?>
    <nav class="sidebar-menu">
        <h2>Pages Menu</h2>
        <?php buildMenu($articles, $this); ?>
    </nav>
    <?php endif; ?>
</div>