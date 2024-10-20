<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<div class="tag-articles">
    <h2 class="mb-4 text-primary"><?= h($tag->title) ?>: <?= __('Associated Pages') ?></h2>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title h5 mb-0"><?= __('Associated Pages') ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($tag->articles)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($tag->articles as $article): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="h6 mb-1">
                                        <?= $this->Html->link(
                                            h($article->title),
                                            '/' . $article->slug,
                                            ['class' => 'text-primary']
                                        ) ?>
                                    </h4>
                                    <small class="text-muted"><?= __('By') ?> <?= h($article->user->username) ?></small>
                                </div>
                                <small class="text-muted"><?= h($article->created->format('F j, Y, g:i a')) ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="card-text"><?= __('No articles found for this tag.') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>