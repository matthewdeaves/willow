<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Aiprompt> $aiprompts
 */
?>
<div class="aiprompts index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('AI Prompts') ?></h3>
        <?= $this->Html->link(__('New AI Prompt'), ['action' => 'add'], ['class' => 'btn btn-primary my-3 ms-2']) ?>
    </div>
    <div class="mb-3">
        <input type="text" id="aipromptSearch" class="form-control" placeholder="Search AI prompts...">
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= $this->Paginator->sort('task_type') ?></th>
                    <th><?= $this->Paginator->sort('model') ?></th>
                    <th><?= $this->Paginator->sort('max_tokens') ?></th>
                    <th><?= $this->Paginator->sort('temperature') ?></th>
                    <th><?= $this->Paginator->sort('created_at', 'Created') ?></th>
                    <th><?= $this->Paginator->sort('modified_at', 'Modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aiprompts as $aiprompt): ?>
                <tr>
                    <td><?= h($aiprompt->task_type) ?></td>
                    <td><?= h($aiprompt->model) ?></td>
                    <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
                    <td><?= $this->Number->format($aiprompt->temperature) ?></td>
                    <td><?= h($aiprompt->created_at->format('Y-m-d H:i')) ?></td>
                    <td><?= h($aiprompt->modified_at->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $aiprompt->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiprompt->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($aiprompts)]) ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('aipromptSearch');
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
                })
                .catch(error => console.error('Error:', error));
            } else {
                location.reload();
            }
        }, 300); // Debounce for 300ms
    });
});
<?php $this->Html->scriptEnd(); ?>