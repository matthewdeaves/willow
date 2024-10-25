<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Internationalisation> $internationalisations
 */
?>
<div class="internationalisations index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Internationalisations') ?></h3>
        <?= $this->Html->link(__('New Internationalisation'), ['action' => 'add'], ['class' => 'btn btn-primary my-3 ms-2']) ?>
    </div>
    <div class="mb-3">
    <?= $this->element('locale_filters', ['tags' => $locales, 'selectedTag' => $selectedLocale]) ?>
    </div>
    <div class="mb-3">
        <input type="text" id="internationalisationSearch" class="form-control" placeholder="Search Internationalisations...">
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= $this->Paginator->sort('locale') ?></th>
                    <th><?= $this->Paginator->sort('message_id') ?></th>
                    <th><?= $this->Paginator->sort('message_str') ?></th>
                    <th><?= $this->Paginator->sort('created_at') ?></th>
                    <th><?= $this->Paginator->sort('updated_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($internationalisations as $internationalisation): ?>
                <tr>
                    <td><?= h($internationalisation->locale) ?></td>
                    <td><?= h($internationalisation->message_id) ?></td>
                    <td><?= h($internationalisation->message_str) ?></td>
                    <td><?= h($internationalisation->created_at) ?></td>
                    <td><?= h($internationalisation->updated_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $internationalisation->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $internationalisation->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $internationalisation->id], ['confirm' => __('Are you sure you want to delete # {0}?', $internationalisation->message_id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($internationalisations)]) ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('internationalisationSearch');
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