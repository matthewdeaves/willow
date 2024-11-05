<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<div class="articles">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-start mb-3">
                <?php if (!empty($article->image)) : ?>
                <div class="me-3">
                    <?= $this->Html->image(SettingsManager::read('ImageSizes.teeny') . '/' . $article->image, 
                        [
                            'pathPrefix' => 'files/Articles/image/', 
                            'alt' => $article->alt_text, 
                            'class' => 'img-thumbnail', 
                            'data-bs-toggle' => 'popover', 
                            'data-bs-trigger' => 'hover', 
                            'data-bs-html' => 'true', 
                            'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.extra-large') . '/' . $article->image, 
                            [
                                'pathPrefix' => 'files/Articles/image/', 
                                'alt' => $article->alt_text, 
                                'class' => 'img-fluid', 
                                'style' => 'max-width: 400px; max-height: 400px;'
                            ])
                        ]) 
                    ?>
                </div>
                <?php endif; ?>
                <h1 class="card-title mb-0"><?= htmlspecialchars_decode($article->title) ?></h1>
            </div>
            
            <p class="card-text text-muted">
                <?= __('By') ?> <?= $article->user ? htmlspecialchars_decode($article->user->username) : __('Unknown Author') ?> | 
                <?php if ($article->published): ?>
                    <?= $article->published->format('F j, Y, g:i a') ?>
                <?php else: ?>
                    <?= __('Not published') ?>
                <?php endif; ?>
            </p>
            <div class="article-content">
                <?= $article->body ?>
            </div>
            <div>
                <?= $this->element('image_carousel', [
                    'images' => $article->images,
                    'carouselId' => 'articleImagesCarousel'
                ]) ?>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h3 class="card-title"><?= __('Tags') ?></h3>
            <?php if (!empty($article->tags)): ?>
                <?= $this->element('tags', ['article' => $article]) ?>
            <?php else: ?>
                <p class="text-muted"><?= __('No tags are linked.') ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php if(
            (SettingsManager::read('Comments.articlesEnabled') && $article->kind == 'article')
            || (SettingsManager::read('Comments.pagesEnabled') && $article->kind == 'page')
        ):
    ?>
        <section class="comments mb-4">
            <h3 class="mb-3"><?= __('Comments') ?></h3>
            <?php if (!empty($article->comments)): ?>
                <?php foreach ($article->comments as $comment): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="card-text"><?= htmlspecialchars_decode($comment->content) ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <?= __('Posted on {date} by {author}', [
                                        'date' => $comment->created->format('F j, Y, g:i a'),
                                        'author' => h($comment->user->username ?? 'Unknown')
                                    ]) ?>
                                </small>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted"><?= __('No comments yet.') ?></p>
            <?php endif; ?>           
        </section>
        <?php if ($this->Identity->isLoggedIn()): ?>
            <section class="add-comment mb-4">
                <h3 class="mb-3"><?= __('Add a Comment') ?></h3>
                <?= $this->Form->create(null, ['url' => ['controller' => 'Articles', 'action' => 'addComment', $article->id]]) ?>
                    <?= $this->Form->control('content', ['label' => __('Comment'), 'type' => 'textarea', 'rows' => 3, 'class' => 'form-control mb-3']) ?>
                    <?= $this->Form->button(__('Submit Comment'), ['class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
            </section>
        <?php else: ?>
            <section class="login-prompt mb-4">
                <h3 class="mb-3"><?= __('Please log in to add a comment') ?></h3>
                <?= $this->Html->link(__('Login'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'btn btn-primary']) ?>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php $this->Html->script('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', ['block' => true]); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body'
        })
    })
});
</script>