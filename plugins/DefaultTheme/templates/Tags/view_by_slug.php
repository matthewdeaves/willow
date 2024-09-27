<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<div class="container mt-4">
    <h1 class="mb-4"><?= h($tag->title) ?></h1>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="card-title h4 mb-0">Tag Details</h2>
        </div>
        <div class="card-body">
            <p class="card-text"><strong>Slug:</strong> <?= h($tag->slug) ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="card-title h4 mb-0">Associated Articles</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($tag->articles)): ?>
                <div class="list-group">
                    <?php foreach ($tag->articles as $article): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">
                                    <?= $this->Html->link(h($article->title), ['controller' => 'Articles', 'action' => 'viewBySlug', $article->slug], ['class' => 'text-primary']) ?>
                                </h5>
                                <small><?= h($article->created->format('M d, Y')) ?></small>
                            </div>
                            <p class="mb-1">By <?= h($article->user->username) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="card-text">No articles found for this tag.</p>
            <?php endif; ?>
        </div>
    </div>
</div>