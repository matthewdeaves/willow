<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<?php if (!empty($article->tags)) : ?>
<div class="related-tags mb-4">
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($article->tags as $tag) : ?>
            <?= $this->Html->link(
                h($tag->title),
                ['controller' => 'Tags', 'action' => 'view-by-slug', $tag->slug],
                ['class' => 'btn btn-outline-primary btn-sm']
            ) ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>