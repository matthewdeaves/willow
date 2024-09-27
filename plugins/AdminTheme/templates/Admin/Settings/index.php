<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

/**
 * @var \App\View\AppView $this
 * @var array $settings
 */
?>
<div class="settings index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Settings') ?></h3>
    </div>
    <?php foreach ($settings as $groupName => $groupSettings): ?>
        <h4 class="mt-4"><?= Inflector::humanize(Inflector::underscore($groupName)) ?></h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th><?= __('Key Name') ?></th>
                        <th><?= __('Value') ?></th>
                        <th><?= __('Modified') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupSettings as $setting): ?>
                    <tr>
                        <td><?= h($setting->key_name) ?></td>
                        <td><?= h($setting->value) ?></td>
                        <td><?= h($setting->modified->format('Y-m-d H:i')) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $setting->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $setting->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $setting->id], ['confirm' => __('Are you sure you want to delete {0}?', $setting->key_name), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>