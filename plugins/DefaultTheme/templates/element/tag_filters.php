<?php
/**
 * @var \App\View\AppView $this
 * @var array $tags
 * @var string|null $selectedTag
 */
?>
<div class="mb-3">
    <div class="btn-group" role="group" aria-label="Tag filters">
        <?= $this->Html->link(__('All Tags'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary' . (!$selectedTag ? ' active' : '')]) ?>
        <?php foreach ($tags as $tag): ?>
            <?= $this->Html->link(
                h($tag),
                ['action' => 'index', '?' => ['tag' => $tag]],
                ['class' => 'btn btn-outline-secondary' . ($selectedTag === $tag ? ' active' : '')]
            ) ?>
        <?php endforeach; ?>
    </div>
</div>