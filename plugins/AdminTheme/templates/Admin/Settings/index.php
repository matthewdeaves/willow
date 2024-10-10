<?php
/**
 * @var \App\View\AppView $this
 * @var array $groupedSettings
 */

use Cake\Utility\Inflector;
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><?= __('Edit Settings') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, ['url' => ['action' => 'saveSettings'], 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <?php foreach ($groupedSettings as $category => $settings): ?>
                        <h4 class="mb-3 mt-4 text-secondary"><?= h(Inflector::humanize($category)) ?></h4>
                        <div class="row">
                        <?php foreach ($settings as $key => $setting): ?>
                            <div class="col-md-4 mb-3">
                                <?php
                                $value = $setting['value'];
                                $isNumeric = $setting['is_numeric'];
                                ?>
                                <?= $this->Form->control("{$category}.{$key}", [
                                    'label' => Inflector::humanize($key),
                                    'value' => $value,
                                    'class' => 'form-control' . ($isNumeric ? ' is-numeric' : ''),
                                    'type' => $isNumeric ? 'number' : 'text',
                                    'min' => $isNumeric ? 0 : null,
                                    'step' => $isNumeric ? 1 : null,
                                    'placeholder' => $isNumeric ? __('Enter a number') : __('Enter text')
                                ]) ?>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <?= $this->Form->button(__('Save All Settings'), ['class' => 'btn btn-primary mt-3']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>