<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>

<article class="blog-post">
    <header class="article-header mb-4">
        <h1 class="display-5 link-body-emphasis mb-3"><?= htmlspecialchars_decode($article->title) ?></h1>
        <div class="blog-post-meta">
            <span class="date"><?= $article->published->format('F j, Y') ?></span>
            <span class="author">by <?= h($article->user->username) ?></span>
        </div>
    </header>
    
    <?php if (!empty($article->image)): ?>
    <div class="article-featured-image mb-4">
        <?= $this->element('image/icon', [
            'model' => $article, 
            'icon' => $article->largeImageUrl, 
            'preview' => false,
            'class' => 'img-fluid rounded shadow-sm w-100'
        ]); ?>
    </div>
    <?php endif; ?>
    
    <?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/ui/trumbowyg.min.css'); ?>
    
    <div class="article-content-wrapper">
        <div id="article-body-content" class="article-body trumbowyg-editor-visible trumbowyg-semantic"><?php
            $content = $article->body;
            // Process videos first
            $content = $this->Video->processVideoPlaceholders($content);
            // Process galleries second
            $content = $this->Gallery->processGalleryPlaceholders($content);
            // Enhance content formatting (alignment, responsive images)
            $content = $this->Content->enhanceContent($content, ['processResponsiveImages' => true]);
            echo htmlspecialchars_decode($content);
        ?></div>
    </div>
    
    <?= $this->element('site/facebook/share_button') ?>
    <div class="mb-3">
        <?= $this->element('image_carousel', [
            'images' => $article->images,
            'carouselId' => 'articleImagesCarousel'
        ]) ?>
    </div>

    <div>
        <?= $this->element('article/tags', ['article' => $article]) ?>
    </div>
</article>

<?php
use App\Utility\SettingsManager;

if (
    (SettingsManager::read('Comments.articlesEnabled') && $article->kind == 'article') ||
    (SettingsManager::read('Comments.pagesEnabled') && $article->kind == 'page')
):
?>
    <?= $this->cell('DefaultTheme.ArticleComments', ['article' => $article]) ?>
<?php endif; ?>