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
<article class="blog-post">
    <?= $this->Html->image(SettingsManager::read('ImageSizes.teeny') . '/' . $article->image, 
        [
            'pathPrefix' => 'files/Articles/image/', 
            'alt' => htmlspecialchars_decode($article->alt_text), 
            'class' => 'img-thumbnail article-image', 
            'data-bs-toggle' => 'popover', 
            'data-bs-trigger' => 'hover', 
            'data-bs-html' => 'true', 
            'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.extra-large') . '/' . $article->image, [
                'pathPrefix' => 'files/Articles/image/', 
                'alt' => htmlspecialchars_decode($article->alt_text), 
                'class' => 'img-fluid', 
                'style' => 'max-width: 400px; max-height: 400px;'
    ])]) ?>
    <h2 class="display-5 link-body-emphasis mb-1"><?= $article->title ?></h2>
    <p class="blog-post-meta">
    <?= $article->published->format('F j, Y') ?> <?= h($article->user->username) ?>
    </p>
    <p><?= $article->lead ?></p>
    <hr>
    <?= htmlspecialchars_decode($article->summary); ?>
    <hr>
    <?= htmlspecialchars_decode($article->body); ?>
</article>
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