<?php
/**
 * @var \App\View\AppView $this
 * @var array $systemLogs
 */
?>
<div class="logs index content container mt-4">
    <?php if (!empty($systemLogs)): ?>
        <?= $this->Form->postLink(
            __('Delete All'),
            ['action' => 'deleteAll'],
            ['confirm' => __('Are you sure you want to delete all logs? This cannot be undone.'), 'class' => 'btn btn-danger float-end mb-3']
        ) ?>
    <?php endif; ?>
    <h3 class="mb-4"><?= __('System Logs') ?></h3>
    <?php if (empty($systemLogs)): ?>
        <div class="alert alert-info" role="alert">No logs found.</div>
    <?php else: ?>
        <?php foreach ($systemLogs as $groupName => $logs): ?>
            <h4 class="mt-4"><?= \Cake\Utility\Inflector::humanize($groupName) ?></h4>
            <div class="table-responsive">
                <?= $this->Form->postLink(
                    __('Delete All {0} Logs', \Cake\Utility\Inflector::humanize($groupName)),
                    ['action' => 'deleteAll', $groupName],
                    ['confirm' => __('Are you sure you want to delete all logs in the {0} group? This cannot be undone.', \Cake\Utility\Inflector::humanize($groupName)), 'class' => 'btn btn-warning mb-3']
                ) ?>
                <table class="table table-striped table-hover">
                    <thead class="table-light">
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
                                <button class="btn btn-sm btn-outline-primary toggle-message" data-log-id="<?= $log->id ?>">Show Message</button>
                                <div class="log-message mt-2" id="log-message-<?= $log->id ?>" style="display: none;">
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="card-text"><?= h($log->message) ?></p>
                                            <p class="card-text"><small class="text-muted"><?= h($log->context) ?></small></p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= h($log->created) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['action' => 'view', $log->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $log->id], ['confirm' => __('Are you sure you want to delete # {0}?', $log->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
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
                    this.classList.replace('btn-outline-primary', 'btn-primary');
                } else {
                    messageDiv.style.display = 'none';
                    this.textContent = 'Show Message';
                    this.classList.replace('btn-primary', 'btn-outline-primary');
                }
            });
        });
    });
</script>