<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<div class="articles">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h1 class="card-title"><?= h($article->title) ?></h1>
            <p class="card-text text-muted">
                <?= __('By') ?> <?= $article->user ? h($article->user->username) : __('Unknown Author') ?> | 
                <?php if ($article->published): ?>
                    <?= $article->published->format('F j, Y, g:i a') ?>
                <?php else: ?>
                    <?= __('Not published') ?>
                <?php endif; ?>
            </p>
            <div class="article-content">
                <?= $article->body ?>
            </div>
            <?php if (!empty($article->tags)): ?>
                <?= $this->element('tags', ['article' => $article]) ?>
            <?php else: ?>
                <p class="text-muted"><?= __('No tags available.') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <section class="comments mb-4">
        <h3 class="mb-3"><?= __('Comments') ?></h3>
        <?php if (!empty($article->comments)): ?>
            <?php foreach ($article->comments as $comment): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <p class="card-text"><?= h($comment->content) ?></p>
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

    <section class="add-comment mb-4">
        <h3 class="mb-3"><?= __('Add a Comment') ?></h3>
        <?= $this->Form->create(null, ['url' => ['controller' => 'Articles', 'action' => 'addComment', $article->id]]) ?>
            <?= $this->Form->control('content', ['label' => __('Comment'), 'type' => 'textarea', 'rows' => 3, 'class' => 'form-control mb-3']) ?>
            <?= $this->Form->button(__('Submit Comment'), ['class' => 'btn btn-primary']) ?>
        <?= $this->Form->end() ?>
    </section>
</div>