<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\SystemLog> $systemLogs
 */
?>
<div class="systemLogs index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('System Logs') ?></h3>
        <?= $this->Html->link(__('New System Log'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
    </div>
    <div class="mb-3">
        <input type="text" id="logSearch" class="form-control" placeholder="<?= __('Search logs...') ?>">
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th><?= $this->Paginator->sort('level') ?></th>
                    <th><?= $this->Paginator->sort('group_name') ?></th>
                    <th><?= $this->Paginator->sort('message') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($systemLogs as $systemLog): ?>
                <tr>
                    <td><?= h($systemLog->level) ?></td>
                    <td><?= h($systemLog->group_name) ?></td>
                    <td>
                        <?php
                        $truncatedMessage = substr($systemLog->message, 0, 50) . '...';
                        $fullMessage = h($systemLog->message);
                        $prettyMessage = $this->element('pretty_message', ['message' => $fullMessage]);
                        ?>
                        <span tabindex="0" class="d-inline-block" data-bs-toggle="popover" data-bs-trigger="focus" title="Full Message" data-bs-content="<?= $prettyMessage ?>" data-bs-html="true">
                            <?= h($truncatedMessage) ?>
                        </span>
                    </td>
                    <td><?= h($systemLog->created->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $systemLog->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $systemLog->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $systemLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $systemLog->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('logSearch');
    const resultsContainer = document.querySelector('tbody');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();

            if (searchTerm.length > 0) {
                fetch(`<?= $this->Url->build(['action' => 'index']) ?>?search=${encodeURIComponent(searchTerm)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                    initPopovers();
                })
                .catch(error => console.error('Error:', error));
            } else {
                location.reload();
            }
        }, 300);
    });

    initPopovers();
});

function initPopovers() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body'
        })
    })
}
<?php $this->Html->scriptEnd(); ?>