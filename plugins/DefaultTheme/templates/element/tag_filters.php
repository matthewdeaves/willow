<?php
/**
 * @var \App\View\AppView $this
 * @var array $tags
 * @var string|null $selectedTag
 */
?>
<?php if (!empty($tags)) : ?>
<div class="mb-3">
    <div class="d-flex flex-wrap gap-2" role="group" aria-label="Tag filters">
        <?= $this->Html->link(
            __('All Tags'),
            [
                '_name' => 'home',
            ],
            [
                'class' => 'btn btn-outline-secondary' . (!$selectedTag ? ' active' : '')
            ]) ?>
        <?php foreach ($tags as $tag): ?>
            <?= $this->Html->link(
                htmlspecialchars_decode($tag),
                [
                    '_name' => 'home',
                    '?' => ['tag' => $tag]
                ],
                ['class' => 'btn btn-outline-secondary' . ($selectedTag === $tag ? ' active' : '')]
            ) ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>