<?php
/**
 * @var \App\View\AppView $this
 * @var array $cacheInfo
 */

function sanitizeId($name) {
    return preg_replace('/[^a-z0-9]/i', '_', $name);
}

function formatDateTime(?DateTime $dateTime): string {
    if ($dateTime === null) {
        return __('Never');
    }
    return $dateTime->format('Y-m-d H:i:s');
}
?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0"><?= __('Cache Management') ?></h3>
        </div>
        <div class="card-body">
            <p><?= __('Below is information about the configured caches. Click the button to clear all caches or individual cache buttons to clear specific caches.') ?></p>

            <?= $this->Form->create(null, ['url' => ['action' => 'clearAll'], 'class' => 'mb-4']) ?>
                <?= $this->Form->button(__('Clear All Cache'), ['class' => 'btn btn-danger']) ?>
            <?= $this->Form->end() ?>

            <h4 class="mb-3"><?= __('Configured Caches') ?></h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?= __('Cache Name') ?></th>
                        <th><?= __('Engine') ?></th>
                        <th><?= __('Last Cleared') ?></th>
                        <th><?= __('Details') ?></th>
                        <th><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cacheInfo as $name => $info): ?>
                        <tr>
                            <td><?= h($name) ?></td>
                            <td><?= h(basename($info['engine'])) ?></td>
                            <td><?= formatDateTime($info['last_cleared']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?= sanitizeId($name) ?>" aria-expanded="false" aria-controls="details-<?= sanitizeId($name) ?>">
                                    <?= __('Show Details') ?>
                                </button>
                            </td>
                            <td>
                                <?= $this->Form->postLink(
                                    __('Clear'),
                                    ['action' => 'clear', urlencode($name)],
                                    ['class' => 'btn btn-sm btn-warning', 'confirm' => __('Are you sure you want to clear {0}?', $name)]
                                ) ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" class="p-0">
                                <div class="collapse" id="details-<?= sanitizeId($name) ?>">
                                    <div class="card card-body">
                                        <h6><?= __('Settings') ?></h6>
                                        <ul class="list-unstyled">
                                            <?php foreach ($info['settings'] as $key => $value): ?>
                                                <li>
                                                    <strong><?= h($key) ?>:</strong> 
                                                    <?php
                                                    if (is_array($value)) {
                                                        echo empty($value) ? '[]' : h(json_encode($value, JSON_PRETTY_PRINT));
                                                    } elseif (is_null($value)) {
                                                        echo 'null';
                                                    } elseif (is_bool($value)) {
                                                        echo $value ? 'true' : 'false';
                                                    } else {
                                                        echo h($value);
                                                    }
                                                    ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>