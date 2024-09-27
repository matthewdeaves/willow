<?php
/**
 * @var \App\View\AppView $this
 * @var array $systemLogs
 */
?>
<div class="logs index content">
    <?php if (!empty($systemLogs)): ?>
        <?= $this->Form->postLink(
            __('Delete All'),
            ['action' => 'deleteAll'],
            ['confirm' => __('Are you sure you want to delete all logs? This cannot be undone.'), 'class' => 'button float-right']
        ) ?>
    <?php endif; ?>
    <h3><?= __('System Logs') ?></h3>
    <?php if (empty($systemLogs)): ?>
        <p>No logs found.</p>
    <?php else: ?>
        <?php foreach ($systemLogs as $groupName => $logs): ?>
            <h4><?= \Cake\Utility\Inflector::humanize($groupName) ?></h4>
            <div class="table-responsive">
                <?= $this->Form->postLink(
                    __('Delete All {0} Logs', \Cake\Utility\Inflector::humanize($groupName)),
                    ['action' => 'deleteAll', $groupName],
                    ['confirm' => __('Are you sure you want to delete all logs in the {0} group? This cannot be undone.', \Cake\Utility\Inflector::humanize($groupName)), 'class' => 'button']
                ) ?>
                <table>
                    <thead>
                        <tr>
                            <th>Message</th>
                            <th>Created</th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <button class="toggle-message" data-log-id="<?= $log->id ?>">Show Message</button>
                                <div class="log-message" id="log-message-<?= $log->id ?>" style="display: none;">
                                    <?= h($log->message) ?>
                                    <?= h($log->context) ?>
                                </div>
                            </td>
                            <td><?= h($log->created) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['action' => 'view', $log->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $log->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $log->id], ['confirm' => __('Are you sure you want to delete # {0}?', $log->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.toggle-message');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const logId = this.getAttribute('data-log-id');
                const messageDiv = document.getElementById('log-message-' + logId);
                if (messageDiv.style.display === 'none') {
                    messageDiv.style.display = 'block';
                    this.textContent = 'Hide Message';
                } else {
                    messageDiv.style.display = 'none';
                    this.textContent = 'Show Message';
                }
            });
        });
    });
</script>