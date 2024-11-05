<?php
/**
 * @var \App\View\AppView $this
 * @var array $tags
 * @var string|null $selectedTagId
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
                'class' => 'btn btn-outline-secondary px-3 py-2' . (!$selectedTagId ? ' active' : '')
            ]) ?>
        <?php foreach ($tags as $id => $title): ?>
            <?= $this->Html->link(
                htmlspecialchars_decode($title),
                [
                    '_name' => 'home',
                    '?' => ['tag' => $id]
                ],
                ['class' => 'btn btn-outline-secondary px-3 py-2' . ($selectedTagId == $id ? ' active' : '')]
            ) ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>