<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<div class="users index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Users') ?></h3>
        <?= $this->Html->link(__('New User'), ['prefix' => 'Admin', 'action' => 'add'], ['class' => 'btn btn-primary my-3 ms-2']) ?>
    </div>
    <div class="mb-3">
        <input type="text" id="userSearch" class="form-control" placeholder="<?= __('Search users...') ?>">
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Picture') ?></th>
                    <th><?= $this->Paginator->sort('username') ?></th>
                    <th><?= $this->Paginator->sort('email') ?></th>
                    <th><?= $this->Paginator->sort('is_admin', 'Admin') ?></th>
                    <th><?= $this->Paginator->sort('is_disabled', 'Enabled') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <?php if (!empty($user->picture)) : ?>
                        <div class="position-relative">
                            <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $user->picture, 
                                ['pathPrefix' => 'files/Users/picture/', 
                                'alt' => $user->alt_text, 
                                'class' => 'img-thumbnail', 
                                'width' => '50',
                                'data-bs-toggle' => 'popover',
                                'data-bs-trigger' => 'hover',
                                'data-bs-html' => 'true',
                                'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $user->picture, 
                                    ['pathPrefix' => 'files/Users/picture/', 
                                    'alt' => $user->alt_text, 
                                    'class' => 'img-fluid', 
                                    'style' => 'max-width: 300px; max-height: 300px;'])
                                ]) 
                            ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><?= h($user->username) ?></td>
                    <td><?= h($user->email) ?></td>
                    <td><?= $user->is_admin ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-secondary">' . __('No') . '</span>' ?></td>
                    <td><?= $user->is_disabled ? '<span class="badge bg-danger">' . __('No') . '</span>' : '<span class="badge bg-success">' . __('Yes') . '</span>' ?></td>
                    <td><?= h($user->created->format('Y-m-d H:i')) ?></td>
                    <td><?= h($user->modified->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $user->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?php if ($this->Identity->get('id') != $user->id) : ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete {0}?', $user->username), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($users)]) ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
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
                // If search is empty, you might want to reload all results or clear the table
                location.reload();
            }
        }, 300); // Debounce for 300ms
    });
});
<?php $this->Html->scriptEnd(); ?>