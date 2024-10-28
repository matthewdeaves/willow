<?php
/**
 * @var \App\View\AppView $this
 * @var array $tags
 * @var string|null $selectedSiteLanguage
 */
?>
<?php
$currentParams = $this->request->getAttribute('params');
unset($currentParams['_matchedRoute']);
unset($currentParams['pass']);
?>
<div class="mb-3">
    <div class="d-flex flex-wrap gap-2" role="group" aria-label="Language filters">
    <?php $currentParams['lang'] = 'en'; ?>
        <?= $this->Html->link(
            __('English'),
            $currentParams,
            [
                'class' => 'btn btn-outline-secondary' . ($selectedSiteLanguage === 'en' ? ' active' : '')
            ]) ?>
        <?php foreach ($languages as $code => $name): ?>
            <?php $currentParams['lang'] = $code; ?>
            <?= $this->Html->link(
                h($name),
                $currentParams,
                ['class' => 'btn btn-outline-secondary' . ($selectedSiteLanguage == $code ? ' active' : '')]
            ) ?>
        <?php endforeach; ?>
    </div>
</div>