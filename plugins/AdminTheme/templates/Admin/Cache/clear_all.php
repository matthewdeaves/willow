<?php
/**
 * @var \App\View\AppView $this
 * @var array $clearedCaches
 * @var array $failedCaches
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><?= __('Clear All Cache') ?></h3>
                </div>
                <div class="card-body">
                    <p><?= __('Click the button below to clear all cache in the application.') ?></p>

                    <?= $this->Form->create(null, ['url' => ['action' => 'clearAll'], 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="text-center">
                        <?= $this->Form->button(__('Clear All Cache'), ['class' => 'btn btn-danger mt-3']) ?>
                    </div>
                    <?= $this->Form->end() ?>

                    <?php if (isset($clearedCaches) && !empty($clearedCaches)): ?>
                        <h4 class="mb-3 mt-4 text-success"><?= __('Cleared Caches') ?></h4>
                        <ul class="list-group">
                            <?php foreach ($clearedCaches as $cache): ?>
                                <li class="list-group-item"><?= h($cache) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (isset($failedCaches) && !empty($failedCaches)): ?>
                        <h4 class="mb-3 mt-4 text-danger"><?= __('Failed to Clear Caches') ?></h4>
                        <ul class="list-group">
                            <?php foreach ($failedCaches as $cache): ?>
                                <li class="list-group-item"><?= h($cache) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>