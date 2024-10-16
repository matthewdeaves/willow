<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<div class="articles">
    <?php foreach ($articles as $article): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
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
                    <h2 class="card-title mb-0">
                        <?= $this->Html->link(
                            h($article->title),
                            ['controller' => 'Articles', 'action' => 'view', $article->slug],
                            ['class' => 'text-decoration-none']
                        ) ?>
                    </h2>
                </div>
                <p class="card-text text-muted">
                    <?= __('By') ?> <?= h($article->user->username) ?> | 
                    <?= $article->published->format('F j, Y, g:i a') ?>
                </p>
                <div class="article-content">
                    <div class="article-preview">
                        <?= $this->Text->truncate($article->body, 200, ['ellipsis' => '...', 'exact' => false]); ?>
                    </div>
                    <div class="article-full collapse" id="article-<?= $article->id ?>">
                        <?= $article->body; ?>
                    </div>
                </div>
                <button class="btn btn-secondary show-full-article" data-bs-toggle="collapse" data-bs-target="#article-<?= $article->id ?>">
                    <?= __('Show Full Article') ?>
                </button>
                <?= $this->Html->link(
                    __('Read More'),
                    ['controller' => 'Articles', 'action' => 'view', $article->slug],
                    ['class' => 'btn btn-primary']
                ) ?>
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

            full.classList.toggle('show');
            if (full.classList.contains('show')) {
                this.textContent = '<?= __('Show Less') ?>';
                preview.style.display = 'none';
            } else {
                this.textContent = '<?= __('Show Full Article') ?>';
                preview.style.display = 'block';
            }
        });
    });
});
</script>