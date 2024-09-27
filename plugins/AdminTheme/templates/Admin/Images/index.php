<?php use Cake\Core\Configure; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<div class="images index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Images') ?></h3>
        <div>
            <?= $this->Html->link(__('New Image'), ['action' => 'add'], ['class' => 'btn btn-primary me-2']) ?>
            <?= $this->Html->link(__('Bulk Upload'), ['action' => 'bulkUpload'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <div class="mb-3">
        <input type="text" id="imageSearch" class="form-control" placeholder="Search images...">
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Picture') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody id="imageResults">
                <?php foreach ($images as $image): ?>
                    <tr>
                        <td>
                            <div class="position-relative">
                                <?= $this->Html->image($image->image_file . '_' . Configure::read('SiteSettings.ImageSizes.small'), 
                                    ['pathPrefix' => 'files/Images/image_file/', 'alt' => 'Picture', 'class' => 'img-thumbnail', 'width' => '50', 'data-bs-toggle' => 'popover', 'data-bs-trigger' => 'hover', 'data-bs-html' => 'true', 'data-bs-content' => $this->Html->image($image->image_file . '_' . Configure::read('SiteSettings.ImageSizes.large'), ['pathPrefix' => 'files/Images/image_file/', 'alt' => 'Picture', 'class' => 'img-fluid', 'style' => 'max-width: 300px; max-height: 300px;'])]) ?>
                            </div>
                        </td>
                        <td><?= h($image->name) ?></td>
                        <td><?= h($image->created->format('Y-m-d H:i')) ?></td>
                        <td><?= h($image->modified->format('Y-m-d H:i')) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $image->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $image->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete {0}?', $image->name), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($images)]) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('imageSearch');
    const resultsContainer = document.getElementById('imageResults');

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
</script>