<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<div class="container mt-4">
    <h1 class="mb-4"><?= h($tag->title) ?>: <?= __('Associated Pages') ?></h1>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="card-title h4 mb-0"><?= __('Associated Pages') ?></h2>
        </div>
        <div class="card-body">
            <?php if (!empty($tag->articles)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($tag->articles as $article): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">
                                        <?= $this->Html->link(
                                            h($article->title),
                                            '/' . $article->slug,
                                            ['class' => 'text-primary']
                                        ) ?>
                                    </h5>
                                    <small class="text-muted">By <?= h($article->user->username) ?></small>
                                </div>
                                <small class="text-muted"><?= h($article->created->format('M d, Y')) ?></small>
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