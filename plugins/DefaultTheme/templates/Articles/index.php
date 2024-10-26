<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 * @var array $tags
 * @var string|null $selectedTag
 */
?>
<div class="articles">
    <?= $this->element('tag_filters', ['tags' => $tags, 'selectedTag' => $selectedTag]) ?>

    <?php foreach ($articles as $article): ?>
        <?php 
        $hasNonEmptySummary = !empty($article->summary);
        $isSmallArticle = !$hasNonEmptySummary && strlen(strip_tags($article->body)) <= 500; 
        ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <?php if (!empty($article->image)) : ?>
                    <div class="me-3">
                        <?= $this->Html->image(SettingsManager::read('ImageSizes.teeny') . '/' . $article->image, 
                            [
                                'pathPrefix' => 'files/Articles/image/', 
                                'alt' => h($article->alt_text), 
                                'class' => 'img-thumbnail article-image', 
                                'data-bs-toggle' => 'popover', 
                                'data-bs-trigger' => 'hover', 
                                'data-bs-html' => 'true', 
                                'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.extra-large') . '/' . $article->image, 
                                [
                                    'pathPrefix' => 'files/Articles/image/', 
                                    'alt' => h($article->alt_text), 
                                    'class' => 'img-fluid', 
                                    'style' => 'max-width: 400px; max-height: 400px;'
                                ])
                            ]) 
                        ?>
                    </div>
                    <?php endif; ?>
                    <h2 class="card-title mb-0">
                        <?= $this->Html->link(
                            h($article->title),
                            [
                                '_name' => 'article-by-slug',
                                'slug' => $article->slug
                            ]
                        ); ?>
                    </h2>
                </div>
                <p class="card-text text-muted">
                    <?= __('By') ?> <?= h($article->user->username) ?> | 
                    <?= $article->published->format('F j, Y, g:i a') ?>
                </p>
                <div class="article-content">
                    <?php if ($hasNonEmptySummary): ?>
                        <div class="article-summary">
                            <?= $article->summary; ?>
                        </div>
                    <?php elseif ($isSmallArticle): ?>
                        <div class="article-full">
                            <?= $article->body; ?>
                        </div>
                    <?php else: ?>
                        <div class="article-preview">
                            <?= $this->Text->truncate($article->body, 200, ['ellipsis' => '...', 'exact' => false]); ?>
                        </div>
                        <div class="article-full" style="display: none;">
                            <?= $article->body; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!$hasNonEmptySummary && !$isSmallArticle): ?>
                    <button class="btn btn-secondary show-full-article" data-article-id="<?= $article->id ?>">
                        <?= __('Show Full Article') ?>
                    </button>
                <?php endif; ?>
                    <?= $this->Html->link(
                        __('Read More'),
                        [
                            '_name' => 'article-by-slug',
                            'slug' => $article->slug
                        ],
                        ['class' => 'btn btn-primary']
                    ); ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?= $this->element('pagination', ['recordCount' => count($articles)]) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body'
        })
    })

    // Show/hide full article
    document.querySelectorAll('.show-full-article').forEach(function (button) {
        button.addEventListener('click', function () {
            var card = this.closest('.card-body');
            var preview = card.querySelector('.article-preview');
            var full = card.querySelector('.article-full');

            if (full.style.display === 'none') {
                full.style.display = 'block';
                preview.style.display = 'none';
                this.textContent = '<?= __('Show Less') ?>';
            } else {
                full.style.display = 'none';
                preview.style.display = 'block';
                this.textContent = '<?= __('Show Full Article') ?>';
            }
        });
    });
});
</script>