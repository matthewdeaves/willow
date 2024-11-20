<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>

<article class="blog-post">

    <h2 class="display-5 link-body-emphasis mb-1"><?= htmlspecialchars_decode($article->title) ?></h2>
    <?= $this->element('image/icon',  ['model' => $article, 'icon' => $article->teenyImageUrl, 'preview' => $article->largeImageUrl ]); ?>
    <p class="blog-post-meta">
        <?= $article->published->format('F j, Y') ?> <?= h($article->user->username) ?>
    </p>
    <div id="article-body-content"><?= $article->body ?></div>
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

<?php if(
        (SettingsManager::read('Comments.articlesEnabled') && $article->kind == 'article')
        || (SettingsManager::read('Comments.pagesEnabled') && $article->kind == 'page')
    ):
?>
    <section class="comments mb-8">
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