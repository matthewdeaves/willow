<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 * @var array $tags
 * @var string|null $selectedTag
 */
?>
<?php foreach ($articles as $article): ?>
<div class="card article-preview-card mb-4">
    <?php if (!empty($article->image)): ?>
    <div class="row g-0">
        <div class="col-md-4">
            <a href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>">
                <?= $this->element('image/icon', [
                    'model' => $article, 
                    'icon' => $article->mediumImageUrl, 
                    'preview' => false,
                    'class' => 'card-img article-preview-image w-100 h-100'
                ]); ?>
            </a>
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <a class="text-decoration-none" href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>">
                    <h2 class="card-title h4 link-body-emphasis"><?= htmlspecialchars_decode($article->title) ?></h2>
                </a>
                <div class="article-meta">
                    <span class="date"><?= $article->published->format('F j, Y') ?></span> • 
                    <span class="author"><?= h($article->user->username) ?></span>
                </div>
                
                <?php $displayMode = SettingsManager::read('Blog.articleDisplayMode', 'summary') ?>
                <div class="article-summary">
                    <?php if ($displayMode == 'lede') : ?>
                        <p class="card-text"><?= htmlspecialchars_decode($article->lede) ?></p>
                    <?php elseif ($displayMode == 'summary') : ?>
                        <p class="card-text"><?= htmlspecialchars_decode($article->summary); ?></p>
                    <?php elseif ($displayMode == 'body') : ?>
                        <div class="card-text"><?= htmlspecialchars_decode($this->Video->processYouTubePlaceholders($article->body)); ?></div>
                    <?php endif; ?>
                </div>
                
                <a href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>" class="btn btn-outline-primary btn-sm">
                    <?= __('Read more') ?>
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card-body">
        <a class="text-decoration-none" href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>">
            <h2 class="card-title h4 link-body-emphasis"><?= htmlspecialchars_decode($article->title) ?></h2>
        </a>
        <div class="article-meta">
            <span class="date"><?= $article->published->format('F j, Y') ?></span> • 
            <span class="author"><?= h($article->user->username) ?></span>
        </div>
        
        <?php $displayMode = SettingsManager::read('Blog.articleDisplayMode', 'summary') ?>
        <div class="article-summary">
            <?php if ($displayMode == 'lede') : ?>
                <p class="card-text"><?= htmlspecialchars_decode($article->lede) ?></p>
            <?php elseif ($displayMode == 'summary') : ?>
                <p class="card-text"><?= htmlspecialchars_decode($article->summary); ?></p>
            <?php elseif ($displayMode == 'body') : ?>
                <div class="card-text"><?= htmlspecialchars_decode($this->Video->processYouTubePlaceholders($article->body)); ?></div>
            <?php endif; ?>
        </div>
        
        <a href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>" class="btn btn-outline-primary btn-sm">
            <?= __('Read more') ?>
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body'
        })
    })
});
</script>