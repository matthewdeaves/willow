<section class="comments mb-8">
    <h3 class="mb-3"><?= __('Comments') ?></h3>
    <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $comment): ?>
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
        <?= $this->Form->create(null, ['url' => ['plugin' => false, 'controller' => 'Articles', 'action' => 'addComment', $article->id]]) ?>
            <?= $this->Form->control('content', ['label' => __('Comment'), 'type' => 'textarea', 'rows' => 3, 'class' => 'form-control mb-3']) ?>
            <?= $this->Form->button(__('Submit Comment'), ['class' => 'btn btn-primary']) ?>
        <?= $this->Form->end() ?>
    </section>
<?php else: ?>
    <section class="login-prompt mb-4">
        <h3 class="mb-3"><?= __('Please log in to add a comment') ?></h3>
        <?= $this->Html->link(__('Login'), ['plugin' => false, 'controller' => 'Users', 'action' => 'login'], ['class' => 'btn btn-primary']) ?>
    </section>
<?php endif; ?>
