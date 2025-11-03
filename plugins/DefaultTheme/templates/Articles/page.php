<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>

<article class="blog-post">
    <h2 class="display-5 link-body-emphasis mb-1"><?= htmlspecialchars_decode($article->title) ?></h2>

    <?= $this->element('site/crumbs') ?>

    <?= $this->element('image/icon', ['model' => $article, 'icon' => $article->smallImageUrl, 'preview' => $article->largeImageUrl]); ?>

    <div id="article-body-content"><?php
        $content = $article->body;
        $content = $this->Video->processVideoPlaceholders($content);
        $content = $this->Gallery->processGalleryPlaceholders($content);
        echo htmlspecialchars_decode($content);
    ?></div>

    <div>
        <?= $this->element('image_carousel', [
            'images' => $article->images,
            'carouselId' => 'articleImagesCarousel'
        ]) ?>
    </div>
</article>

<?php
if (
    (SettingsManager::read('Comments.articlesEnabled') && $article->kind == 'article') ||
    (SettingsManager::read('Comments.pagesEnabled') && $article->kind == 'page')
):
?>
    <?= $this->cell('DefaultTheme.ArticleComments', ['article' => $article]) ?>
<?php endif; ?>