<?php
use Cake\Core\Configure;
/**
 * @var \App\View\AppView $this
 * @var array $settings
 */
?>
<div class="settings index content">
    <h3><?= __('Settings') ?></h3>
    <?php foreach ($settings as $groupName => $groupSettings): ?>
        <h4><?= h($groupName) ?></h4>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Key Name</th>
                        <th>Value</th>
                        <th>Modified</th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupSettings as $setting): ?>
                    <tr>
                        <td><?= h($setting->key_name) ?></td>
                        <td><?= h($setting->value) ?></td>
                        <td><?= h($setting->modified) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $setting->id]) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $setting->id]) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $setting->id], ['confirm' => __('Are you sure you want to delete # {0}?', $setting->id)]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>
