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
    <a class="text-decoration-none" href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>">
        <h2 class="display-5 link-body-emphasis mb-1"><?= $article->title ?></h2>
        <?= $this->element('image/icon',  ['model' => $article, 'icon' => $article->teenyImageUrl, 'preview' => $article->largeImageUrl ]); ?>
        <p class="blog-post-meta">
        <?= $article->published->format('F j, Y') ?> <?= h($article->user->username) ?>
        </p>
    </a>

    <?php $displayMode = SettingsManager::read('Blog.articleDisplayMode', 'summary') ?>

    <?php if ($displayMode == 'lead') : ?>
        <p><?= htmlspecialchars_decode($article->lead) ?></p>
        <hr>
    <?php elseif ($displayMode == 'summary') : ?>
        <?= htmlspecialchars_decode($article->summary); ?>
        <hr>
    <?php elseif ($displayMode == 'body') : ?>
        <?= htmlspecialchars_decode($article->body); ?>
        <hr>
    <?php endif; ?>

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