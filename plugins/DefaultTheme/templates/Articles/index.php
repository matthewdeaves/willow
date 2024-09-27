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
                <h2 class="card-title">
                    <?= $this->Html->link(
                        h($article->title),
                        ['controller' => 'Articles', 'action' => 'viewBySlug', $article->slug],
                        ['class' => 'text-decoration-none']
                    ) ?>
                </h2>
                <p class="card-text text-muted">
                    <?= __('By') ?> <?= h($article->user->username) ?> | 
                    <?= $article->created->format('F j, Y, g:i a') ?>
                </p>
                <div class="article-content">
                    <p class="card-text article-preview">
                        <?= $this->Text->truncate(
                            $article->body,
                            250,
                            [
                                'ellipsis' => '...',
                                'exact' => false
                            ]
                        ) ?>
                    </p>
                    <div class="article-full" style="display: none;">
                        <?= $article->body ?>
                    </div>
                </div>
                <button class="btn btn-secondary show-full-article" data-article-id="<?= $article->id ?>">
                    <?= __('Show Full Article') ?>
                </button>
                <?= $this->Html->link(
                    __('Read More'),
                    ['controller' => 'Articles', 'action' => 'viewBySlug', $article->slug],
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?= $this->Paginator->first('<< ' . __('First'), ['class' => 'page-item']) ?>
                <?= $this->Paginator->prev('< ' . __('Previous'), ['class' => 'page-item']) ?>
                <?= $this->Paginator->numbers(['class' => 'page-item']) ?>
                <?= $this->Paginator->next(__('Next') . ' >', ['class' => 'page-item']) ?>
                <?= $this->Paginator->last(__('Last') . ' >>', ['class' => 'page-item']) ?>
            </ul>
        </nav>
        <p class="text-center text-muted">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.show-full-article').forEach(function (button) {
        button.addEventListener('click', function () {
            var card = this.closest('.card-body');
            var preview = card.querySelector('.article-preview');
            var full = card.querySelector('.article-full');

            if (full.style.display === 'none') {
                preview.style.display = 'none';
                full.style.display = 'block';
                this.textContent = '<?= __('Show Less') ?>';
            } else {
                preview.style.display = 'block';
                full.style.display = 'none';
                this.textContent = '<?= __('Show Full Article') ?>';
            }
        });
    });
});
</script>