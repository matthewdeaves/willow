<?php
/**
 * @var \App\View\AppView $this
 * @var array $tags
 * @var string|null $selectedSiteLanguage
 */
?>
<div class="mb-3">
    <div class="d-flex flex-wrap gap-2" role="group" aria-label="Tag filters">
        <?= $this->Html->link(
            __('English'),
            [
                '_name' => 'home',
                'lang' => 'en',
            ],
            [
                'class' => 'btn btn-outline-secondary' . ($selectedSiteLanguage === 'en' ? ' active' : '')
            ]) ?>
        <?php foreach ($languages as $code => $name): ?>
            <?= $this->Html->link(
                h($name),
                [
                    '_name' => 'home',
                    'lang' => $code,
                ],
                ['class' => 'btn btn-outline-secondary' . ($selectedSiteLanguage == $code ? ' active' : '')]
            ) ?>
        <?php endforeach; ?>
    </div>
</div>